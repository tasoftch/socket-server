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

namespace TASoft\Server\Test\Response;

use PHPUnit\Framework\TestCase;
use TASoft\Server\Response\JSONResponse;

class JSONResponseTest extends TestCase
{
    public function testEmptyResponse() {
        $r = new JSONResponse();
        $this->assertEquals("[]", $r->getResponse());
    }

    public function testArrayResponse() {
        $r = new JSONResponse([1, 2, 3]);
        $this->assertEquals("[1,2,3]", $r->getResponse());
    }

    public function testObjectResponse() {
        $r = new JSONResponse(['a'=>1, 'b' => 2]);
        $this->assertEquals('{"a":1,"b":2}', $r->getResponse());
    }

    public function testArrayAccess() {
        $r = new JSONResponse([1, 2, 3]);
        $r[0] = 10;
        $this->assertEquals([10, 2, 3], $r->getData());
    }

    public function testArrayAppendAccess() {
        $r = new JSONResponse([1, 2, 3]);
        $r[] = 4;
        $this->assertEquals([1, 2, 3, 4], $r->getData());
    }

    public function testReferenceAccess() {
        $r = new JSONResponse([
            [1, 2, 3],
            [4, 5, 6]
        ]);
        $r[0][1] = 10;
        $this->assertEquals([
            [1, 10, 3],
            [4, 5, 6]
        ], $r->getData());
    }
}
