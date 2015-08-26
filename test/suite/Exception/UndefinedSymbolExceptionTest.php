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
use Eloquent\Cosmos\Symbol\SymbolType;
use Exception;
use PHPUnit_Framework_TestCase;

class UndefinedSymbolExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $symbol = Symbol::fromString('\Foo');
        $cause = new Exception();
        $exception = new UndefinedSymbolException($symbol, SymbolType::CLA55(), $cause);

        $this->assertSame($symbol, $exception->symbol());
        $this->assertSame(SymbolType::CLA55(), $exception->type());
        $this->assertSame("Undefined class '\\\\Foo'.", $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($cause, $exception->getPrevious());
    }
}
