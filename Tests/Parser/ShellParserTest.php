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

namespace TASoft\Server\Test\Parser;

use PHPUnit\Framework\TestCase;
use TASoft\Server\Request\ShellRequest;
use TASoft\Server\Session\Parser\ShellRequestParser;

class ShellParserTest extends TestCase
{
    public function testEmptyCommand() {
        $p = new ShellRequestParser();

        $this->assertNull($p->parseRequest(""));
    }

    public function testSingleCommand() {
        $p = new ShellRequestParser();
        /** @var ShellRequest $q */
        $q = $p->parseRequest("ls");
        $this->assertInstanceOf(ShellRequest::class, $q);

        $this->assertEquals('ls', $q->getCommand());
        $this->assertEmpty($q->getArguments());
    }

    public function testCommandWithSimpleArguments() {
        $p = new ShellRequestParser();
        /** @var ShellRequest $q */
        $q = $p->parseRequest("ls ./Testvar -opt 23        -h");

        $this->assertEquals('ls', $q->getCommand());
        $this->assertEquals(['./Testvar', '-opt', '23', '-h'], $q->getArguments());
    }

    public function testCommandWithEscape() {
        $p = new ShellRequestParser();
        /** @var ShellRequest $q */
        $q = $p->parseRequest("ls './Testvar -opt 23'        -h");

        $this->assertEquals('ls', $q->getCommand());
        $this->assertEquals(['./Testvar -opt 23', '-h'], $q->getArguments());
    }

    public function testCommandWithEscape2() {
        $p = new ShellRequestParser();
        /** @var ShellRequest $q */
        $q = $p->parseRequest("ls \"./Testvar -opt 23\"        -h");

        $this->assertEquals('ls', $q->getCommand());
        $this->assertEquals(['./Testvar -opt 23', '-h'], $q->getArguments());
    }

    public function testCommandWithEscape3() {
        $p = new ShellRequestParser();
        /** @var ShellRequest $q */
        $q = $p->parseRequest("ls \"./Testvar 'and \\\"\\\"' -opt 23\"        -h");

        $this->assertEquals('ls', $q->getCommand());
        $this->assertEquals(['./Testvar \'and ""\' -opt 23', '-h'], $q->getArguments());
    }

    public function testCommandBadFormatted() {
        $p = new ShellRequestParser();
        /** @var ShellRequest $q */
        $q = $p->parseRequest("ls \"./Testvar -opt 23        -h");

        $this->assertNull($q);
    }
}
