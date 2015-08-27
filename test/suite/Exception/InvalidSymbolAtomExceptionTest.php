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

use Exception;
use PHPUnit_Framework_TestCase;

/**
 * @covers \Eloquent\Cosmos\Exception\InvalidSymbolAtomException
 */
class InvalidSymbolAtomExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $previous = new Exception();
        $exception = new InvalidSymbolAtomException('foo', $previous);

        $this->assertSame('foo', $exception->atom());
        $this->assertSame("The symbol atom 'foo' contains invalid characters.", $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
