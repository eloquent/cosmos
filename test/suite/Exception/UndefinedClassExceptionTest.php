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

use Eloquent\Cosmos\ClassName\ClassName;
use Exception;
use PHPUnit_Framework_TestCase;

class UndefinedClassExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $className = ClassName::fromString('\Foo');
        $cause = new Exception;
        $exception = new UndefinedClassException($className, $cause);

        $this->assertSame($className, $exception->className());
        $this->assertSame("Undefined class '\\\\Foo'.", $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($cause, $exception->getPrevious());
    }
}
