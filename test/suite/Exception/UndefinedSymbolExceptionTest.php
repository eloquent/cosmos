<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Exception;

use Eloquent\Cosmos\Symbol\Symbol;
use Exception;
use PHPUnit_Framework_TestCase;

/**
 * @covers \Eloquent\Cosmos\Exception\UndefinedSymbolException
 */
class UndefinedSymbolExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $type = 'class';
        $symbol = Symbol::fromString('\Foo\Bar');
        $cause = new Exception();
        $exception = new UndefinedSymbolException($type, $symbol, $cause);

        $this->assertSame($type, $exception->type());
        $this->assertSame($symbol, $exception->symbol());
        $this->assertSame("Undefined class '\\\\Foo\\\\Bar'.", $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($cause, $exception->getPrevious());
    }
}
