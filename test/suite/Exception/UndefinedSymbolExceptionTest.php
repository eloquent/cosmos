<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Exception;

use Eloquent\Cosmos\Symbol\Symbol;
use Exception;
use PHPUnit_Framework_TestCase;

class UndefinedSymbolExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $symbol = Symbol::fromString('\Foo');
        $cause = new Exception;
        $exception = new UndefinedSymbolException($symbol, $cause);

        $this->assertSame($symbol, $exception->symbol());
        $this->assertSame("Undefined symbol '\\\\Foo'.", $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($cause, $exception->getPrevious());
    }
}
