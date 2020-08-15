<?php
/**
 * Copyright (c) 2020 TASoft Applications, Th. Abplanalp <info@tasoft.ch>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace TASoft\Server;


use TASoft\Server\Exception\NoCommunicationSocketException;
use TASoft\Server\Exception\SocketBindException;
use TASoft\Server\Exception\SocketListenException;
use TASoft\Server\Exception\SocketSelectException;
use TASoft\Server\Exception\SocketServerException;
use TASoft\Server\Session\SessionInterface;

abstract class AbstractSocketServer implements SocketServerInterface
{
    /** @var resource */
    private $socket;
    protected $maxClients = 10;
    protected $timeout = -1;
    protected $reuseAddress = true;
    protected $keepConnectionAlive = true;

    /** @var string */
    private $name;



    const BUFFER_CHUNK_SIZE = 2048;

    /**
     * AbstractSocketServer constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * This method gets called once to create a general socket for receiving connections.
     *
     * @return resource
     */
    abstract protected function createCommunicationSocket();

    /**
     * Called to initially configure the communication socket.
     *
     * @param $socket
     */
    protected function configureCommunicationSocket($socket) {
        socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, $this->reuseAddress ? 1 : 0);
    }

    /**
     * Bind the socket to a name
     *
     * @param $socket
     * @return bool
     */
    abstract protected function bindCommunicationSocket($socket): bool;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * This method must properly close the socket and release all occupied resources.
     *
     * @param resource $socket
     */
    abstract protected function closeCommunicationSocket($socket);

    /**
     * If the incoming connection is accepted by your server, it should create a session instance for it.
     *
     * @param $socket
     * @return SessionInterface|null
     */
    abstract protected function acceptConnection($socket): ?SessionInterface;

    /**
     * Notify somehow that the server is not able to accept an incoming connection
     * because the maximum established connection count is reached.
     */
    protected function notifyTooManyConnectionRequests() {
        trigger_error("Could not accept incoming connection because the maximum available connection count is reached", E_USER_WARNING);
    }

    /**
     * Notify that a connection gets dropped.
     *
     * @param $socket
     * @param SessionInterface $session
     */
    protected function dropConnection($socket, SessionInterface $session) {
    }

    /**
     * @return int
     */
    public function getMaxClients(): int
    {
        return $this->maxClients;
    }

    /**
     * @param int $maxClients
     * @return static
     */
    public function setMaxClients(int $maxClients)
    {
        $this->maxClients = $maxClients;
        return $this;
    }

    /**
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * Sets the maximal timeout to block the thread until a connection acquire.
     * Pass -1 for no timeout.
     *
     * @param int $timeout
     * @return static
     */
    public function setTimeout(int $timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * @inheritDoc
     * @throws SocketServerException
     */
    public function run()
    {
        $this->socket = $this->createCommunicationSocket();
        if(!is_resource($this->socket)) {
            throw new NoCommunicationSocketException("Could not create communication seocket");
        }

        $this->configureCommunicationSocket($this->socket);
        if(!$this->bindCommunicationSocket($this->socket)) {
            throw  new SocketBindException("Could not configure and bind socket");
        }

        if(!socket_listen($this->socket, $maximalClients = $this->getMaxClients())) {
            throw new SocketListenException("Could not listen on socket");
        }

        $clients = [];

        if(function_exists('pcntl_signal')) {
            $handler = function() use (&$clients) {
                foreach($clients as $c) {
                    $sock = $c["sock"];
                    /** @var SessionInterface $sess */
                    $sess = $c["sess"];
                    $this->dropConnection($sock, $sess);
                    $sess->serverDroppedConnection($sock);
                }
                echo "All closed. Bye\n";
                exit(0);
            };

            pcntl_signal(SIGINT, $handler, false);
            pcntl_signal(SIGTERM, $handler, false);
        }

        @cli_set_process_title($this->getName());

        while (1) {
            $read = [$this->socket];

            for ($i = 0; $i < $maximalClients; $i++)
            {
                if (isset($clients[$i]))
                    if ($clients[$i]['sock']  != null)
                        $read[$i + 1] = $clients[$i]['sock'] ;
            }
            $write = NULL;
            $except = NULL;

            declare(ticks=1) {
                $ready = @socket_select($read, $write, $except, $this->getTimeout() < 0 ? NULL : $this->getTimeout());
            }

            error_clear_last();

            if(false === $ready) {
                throw new SocketSelectException(socket_strerror( socket_last_error($this->socket) ), socket_last_error($this->socket));
            }

            if(in_array($this->socket, $read)) {
                for($i = 0;$i < $maximalClients;$i++) {
                    if (!isset($clients[$i])) {
                        $clients[$i] = [];
                        $clients[$i]['sock'] = $sock = socket_accept($this->socket);
                        if($sess = $this->acceptConnection($sock)) {
                            $clients[$i]["sess"] = $sess;
                        } else {
                            socket_close($sock);
                            unset($clients[$i]);
                        }
                        break;
                    } elseif ($i == $maximalClients - 1) {
                        $this->notifyTooManyConnectionRequests();
                    }
                }
                continue;
            }

            for($i = 0;$i < $maximalClients;$i++) {
                if (isset($clients[$i])) {
                    if (in_array($clients[$i]['sock'], $read)) {
                        /** @var SessionInterface $sess */
                        $sess = $clients[$i]['sess'];
                        $sock = $clients[$i]['sock'];

                        $length = static::BUFFER_CHUNK_SIZE;
                        if($sess->serverShouldReadFromSocket($sock, $length)) {
                            $buffer = $this->readFromSocket($sock, $length);
                            if(false === $buffer) {
                                $sess->finishTransmission(NULL, false);
                                socket_clear_error($sock);
                                goto dismiss;
                            }


                            if($request = $sess->parseRequest($buffer)) {
                                $response = $this->handleRequest($request);
                                $buffer = $sess->stringifyResponse( $response );

                                if(NULL !== $buffer) {
                                    if($this->writeToSocket($sock, $buffer)) {
                                        $sess->finishTransmission($buffer, true);
                                    } else {
                                        $sess->finishTransmission($buffer, false);
                                        goto dismiss;
                                    }
                                } else {
                                    $sess->finishTransmission($response, false);
                                }
                            } else
                                $sess->finishTransmission($buffer, false);
                        }

                        if(!$this->keepConnectionAlive)
                            goto dismiss;

                        continue;
                        dismiss:
                        $this->dropConnection($sock, $sess);
                        $sess->serverDroppedConnection($sock);
                        unset($clients[$i]);
                        unset($sock);
                        unset($sess);
                    }
                }
            }
        }
    }

    /**
     * Safe reading from socket.
     *
     * @param $socket
     * @param int $chunkSize
     * @return false|string
     */
    protected function readFromSocket($socket, int $chunkSize = 0) {
        if($chunkSize < 1)
            $chunkSize = static::BUFFER_CHUNK_SIZE;

        $buffer = "";
        while (1) {
            $b = socket_read($socket, $chunkSize);
            if(false === $b) {
                return false;
            }
            $buffer .= $b;
            if(strlen($b) < $chunkSize)
                break;
        }
        return $buffer;
    }

    /**
     * Safe writing to socket.
     *
     * @param $socket
     * @param string $data
     * @param int $chunkSize
     * @return bool
     */
    protected function writeToSocket($socket, string $data, int $chunkSize = 0) {
        if($chunkSize < 1)
            $chunkSize = static::BUFFER_CHUNK_SIZE;

        while ($data) {
            $len = socket_write($socket, $data, $chunkSize);
            if(false == $len)
                return false;

            if($len == strlen($data))
                break;
            $data = substr($data, $len);
        }
        return true;
    }
}