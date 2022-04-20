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

namespace TASoft\Server\Session;


use TASoft\Server\Request\PlainTextRequest;
use TASoft\Server\Request\RequestInterface;
use TASoft\Server\Response\PlainTextResponse;
use TASoft\Server\Response\ResponseInterface;
use TASoft\Server\Session\Parser\RequestParserInterface;

abstract class AbstractSession implements SessionInterface
{
    private $socket;
    private $name;
    /** @var RequestParserInterface|null */
    private $requestParser;

    /**
     * AbstractSession constructor.
     * @param $socket
     * @param $name
     */
    public function __construct($socket, $name = "")
    {
        $this->socket = $socket;
        $this->name = $name;
    }


    /**
     * @inheritDoc
     */
    public function serverShouldReadFromSocket($socket, &$length): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function parseRequest(string $receivedData): ?RequestInterface
    {
        if($parser = $this->getRequestParser())
            return $parser->parseRequest($receivedData);

        return new PlainTextRequest( $receivedData );
    }

    /**
     * @inheritDoc
     */
    public function stringifyResponse(?ResponseInterface $response): ?string
    {
        $r = $response->getResponse();
        return is_string($r) ? $r : NULL;
    }

    /**
     * @inheritDoc
     */
    public function finishTransmission($data, bool $success)
    {
    }

    /**
     * @inheritDoc
     */
    public function serverDroppedConnection($socket)
    {
    }

    /**
     * @return resource
     */
    public function getSocket()
    {
        return $this->socket;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return RequestParserInterface|null
     */
    public function getRequestParser(): ?RequestParserInterface
    {
        return $this->requestParser;
    }

    /**
     * @param RequestParserInterface|null $requestParser
     * @return static
     */
    public function setRequestParser(?RequestParserInterface $requestParser)
    {
        $this->requestParser = $requestParser;
        return $this;
    }
}