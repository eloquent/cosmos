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
 * @covers \Eloquent\Cosmos\Exception\ReadException
 */
class ReadExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $path = '/path/to/foo';
        $cause = new Exception();
        $exception = new ReadException($path, $cause);

        $this->assertSame($path, $exception->path());
        $this->assertSame("Unable to read from '/path/to/foo'.", $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($cause, $exception->getPrevious());
    }

    public function testExceptionDefaults()
    {
        $exception = new ReadException();

        $this->assertNull($exception->path());
        $this->assertSame('Unable to read from stream.', $exception->getMessage());
    }
}
