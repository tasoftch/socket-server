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


use TASoft\Collection\DependencyCollection;
use TASoft\Server\Render\RequestRenderDependenciesInterface;
use TASoft\Server\Render\RequestRenderInterface;
use TASoft\Server\Request\RequestInterface;
use TASoft\Server\Response\ResponseInterface;
use TASoft\Server\Session\SessionInterface;

abstract class AbstractRequestRenderServer extends AbstractSocketServer
{
    /** @var DependencyCollection */
    private $renders;

    /** @var callable|null */
    private $sessionConfigHandler;

    /**
     * AbstractRequestRenderServer constructor.
     */
    public function __construct()
    {
        $this->renders = new DependencyCollection();
    }

    /**
     * Adds a render instance to the server
     *
     * @param RequestRenderInterface $render
     * @param null $name
     * @param array $dependencies
     * @return static
     */
    public function addRender(RequestRenderInterface $render, $name = NULL, $dependencies = []) {
        $name = NULL === $name ? $render->getName() : $name;
        if($render instanceof RequestRenderDependenciesInterface)
            $dependencies = $render->getDependencies();
        $this->renders->add($name, $render, $dependencies);
        return $this;
    }

    /**
     * Removes a given render from server
     *
     * @param RequestRenderInterface|string $render
     * @return static
     */
    public function removeRender($render) {
        if($render instanceof RequestRenderInterface)
            $this->renders->removeElement($render);
        else
            $this->renders->remove($render);
        return $this;
    }

    /**
     * Gets all renders
     *
     * @return RequestRenderInterface[]
     */
    public function getRenders() {
        return $this->renders->getOrderedElements();
    }

    /**
     * @inheritDoc
     */
    public function handleRequest(RequestInterface $request): ?ResponseInterface
    {
        $response = NULL;
        foreach($this->getRenders() as $render) {
            $r = $render->renderRequest($request, $response);
            if($r)
                $response = $r;
            if($render->isPropagationStopped())
                break;
        }
        return $response;
    }

    /**
     * @param callable|null $sessionConfigHandler
     * @return static
     */
    public function setSessionConfigHandler(?callable $sessionConfigHandler)
    {
        $this->sessionConfigHandler = $sessionConfigHandler;
        return $this;
    }

    /**
     * @return callable|null
     */
    public function getSessionConfigHandler(): ?callable
    {
        return $this->sessionConfigHandler;
    }
}