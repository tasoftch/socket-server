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


use TASoft\Server\Request\RequestInterface;
use TASoft\Server\Response\ResponseInterface;

interface SessionInterface
{
    /**
     * If the session's socket wants to send data, the session gets asked before.
     * Returning false, the session needs to ensure, that the socket gets free of data.
     *
     * @param resource $socket
     * @param int $length
     * @return bool
     */
    public function serverShouldReadFromSocket($socket, &$length): bool;

    /**
     * The session needs to transform the received data into a valid request.
     *
     * @param string $receivedData
     * @return RequestInterface|null
     */
    public function parseRequest(string $receivedData): ?RequestInterface;

    /**
     * This method must transform the rendered response into plain data to be sent back by the socket.
     * Returning NULL won't send any data back.
     *
     * @param ResponseInterface|null $response
     * @return string|null
     */
    public function stringifyResponse(?ResponseInterface $response): ?string;

    /**
     * This method informs the session, that the data was sent.
     *
     * @param RequestInterface|ResponseInterface|string|null $data
     * @param bool $success
     */
    public function finishTransmission($data, bool $success);

    /**
     * This method gets called right before the connection is dropped.
     * @param resource $socket
     */
    public function serverDroppedConnection($socket);
}