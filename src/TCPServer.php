<?php
/*
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


use TASoft\Server\Session\SessionInterface;
use TASoft\Server\Session\TCPSession;

class TCPServer extends AbstractRequestRenderServer
{
    /** @var string */
    private $address;
    /** @var string */
    private $port;

    /**
     * TCPServer constructor.
     * @param string $address
     * @param string $port
     */
    public function __construct(string $address, string $port)
    {
        $this->address = $address;
        $this->port = $port;
    }


    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @return string
     */
    public function getPort(): string
    {
        return $this->port;
    }

    /**
     * @inheritDoc
     */
    protected function createCommunicationSocket()
    {
        $socket = @socket_create(AF_INET, SOCK_STREAM, 0);
        socket_clear_error();
        return $socket;
    }

    /**
     * @inheritDoc
     */
    protected function bindCommunicationSocket($socket): bool
    {
        return socket_bind($socket, $this->getAddress(), $this->getPort());
    }

    /**
     * @inheritDoc
     */
    protected function closeCommunicationSocket($socket)
    {
        socket_close($socket);
    }

    /**
     * @inheritDoc
     */
    protected function acceptConnection($socket): ?SessionInterface
    {
        socket_getpeername($socket, $name, $port);
        $sess = new TCPSession($socket, $name, $port);
        if($cb = $this->getSessionConfigHandler())
            call_user_func($cb, $sess);
        return $sess;
    }
}