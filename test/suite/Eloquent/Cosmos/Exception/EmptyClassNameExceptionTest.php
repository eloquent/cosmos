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

use Phake;
use PHPUnit_Framework_TestCase;

class EmptyClassNameExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testExeption()
    {
        $previous = Phake::mock('Exception');
        $exception = new EmptyClassNameException($previous);

        $this->assertSame("Class names cannot be empty.", $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
