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
use Eloquent\Phony\Phpunit\Phony;
use Exception;
use PHPUnit_Framework_TestCase;

class UndefinedSymbolExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $type = 'class';
        $symbol = Phony::mock('Eloquent\Cosmos\Symbol\SymbolInterface');
        $symbol->__toString->returns('symbol');
        $cause = new Exception();
        $exception = new UndefinedSymbolException($type, $symbol->mock(), $cause);

        $this->assertSame($type, $exception->type());
        $this->assertSame($symbol->mock(), $exception->symbol());
        $this->assertSame("Undefined class 'symbol'.", $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($cause, $exception->getPrevious());
    }
}
