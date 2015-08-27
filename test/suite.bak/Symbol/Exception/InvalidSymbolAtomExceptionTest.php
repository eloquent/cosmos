<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Symbol\Exception;

use Exception;
use PHPUnit_Framework_TestCase;

class InvalidSymbolAtomExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $previous = new Exception();
        $exception = new InvalidSymbolAtomException('foo', $previous);

        $this->assertSame('foo', $exception->atom());
        $this->assertSame('The atom contains invalid characters for a symbol.', $exception->reason());
        $this->assertSame('Invalid path atom \'foo\'. The atom contains invalid characters for a symbol.', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
