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

namespace TASoft\Server\Render;


use TASoft\Server\Request\RequestInterface;
use TASoft\Server\Response\ResponseInterface;

interface RequestRenderInterface
{
    /**
     * Each render must have a unique name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Render the request into a response.
     * If this method returns null, the response passed into the method is used instead.
     *
     * @param RequestInterface $request
     * @param ResponseInterface|null $response
     * @return ResponseInterface|null
     */
    public function renderRequest(RequestInterface $request, ?ResponseInterface $response): ?ResponseInterface;

    /**
     * Returning false to not continue further rendering.
     *
     * @return bool
     */
    public function isPropagationStopped(): bool;
}