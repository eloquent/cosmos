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

use Eloquent\Cosmos\ClassName;
use Phake;
use PHPUnit_Framework_TestCase;

class ParentExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testExeption()
    {
        $className = ClassName::fromString('Foo');
        $previous = Phake::mock('Exception');
        $exception = new ParentException($className, $previous);

        $this->assertSame("Unable to determine parent for class 'Foo'.", $exception->getMessage());
        $this->assertSame($className, $exception->className());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
