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

namespace TASoft\Server\Session\Parser;


use TASoft\Server\Request\RequestInterface;
use TASoft\Server\Request\ShellRequest;

class ShellRequestParser implements RequestParserInterface
{
    /**
     * @inheritDoc
     */
    public function parseRequest(string $data): ?RequestInterface
    {
        $cmd = "";
        $args = [];
        $str = 0;
        $state = 0;
        $arg = "";

        $append = function($c) use (&$cmd, &$arg, &$args, &$state) {
            if(NULL !== $c) {
                if($state == 0)
                    $cmd .= $c;
                else
                    $arg .= $c;
            } else {
                if($state == 0)
                    $state = 1;
                elseif($arg) {
                    $args[] = $arg;
                    $arg = "";
                }
            }
        };

        for($e=0;$e<strlen($data);$e++) {
            $c = $data[$e];
            if($c == "\\") {
                $append($data[++$e]);
                continue;
            }

            if($str) {
                if($str == 1 && $c == '"' || $str == 2 && $c == "'") {
                    $str = 0;
                    $append(NULL);
                    continue;
                }

                $append($c);
                continue;
            }

            if($str == 0 && $c == '"') {
                $str = 1;
                continue;
            }
            if($str == 0 && $c == "'") {
                $str = 2;
                continue;
            }

            if(preg_match("/\s/", $c)) {
                $append(NULL);
            } else {
                $append($c);
            }
        }

        $append(NULL);
        if($str != 0)
            return NULL;

        return $cmd ? new ShellRequest($cmd, $args) : NULL;
    }
}