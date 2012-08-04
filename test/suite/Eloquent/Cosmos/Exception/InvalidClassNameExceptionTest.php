<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2012 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Exception;

use PHPUnit_Framework_TestCase;

class InvalidClassNameExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testExeption()
    {
        $exception = new InvalidClassNameException('foo');

        $this->assertSame("Invalid class name 'foo'.", $exception->getMessage());
        $this->assertSame('foo', $exception->className());
    }
}
