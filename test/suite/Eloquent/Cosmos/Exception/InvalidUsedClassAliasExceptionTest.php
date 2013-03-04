<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Exception;

use Eloquent\Cosmos\ClassName;
use Phake;
use PHPUnit_Framework_TestCase;

class InvalidUsedClassAliasExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testExeption()
    {
        $className = ClassName::fromString('Foo');
        $previous = Phake::mock('Exception');
        $exception = new InvalidUsedClassAliasException($className, $previous);

        $this->assertSame("Invalid used class alias 'Foo'.", $exception->getMessage());
        $this->assertSame($className, $exception->className());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
