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

use Eloquent\Pathogen\FileSystem\FileSystemPath;
use Exception;
use PHPUnit_Framework_TestCase;

class ReadExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $path = FileSystemPath::fromString('/path/to/foo');
        $cause = new Exception;
        $exception = new ReadException($path, $cause);

        $this->assertSame($path, $exception->path());
        $this->assertSame("Unable to read from '/path/to/foo'.", $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($cause, $exception->getPrevious());
    }

    public function testExceptionDefaults()
    {
        $exception = new ReadException;

        $this->assertNull($exception->path());
        $this->assertSame("Unable to read from stream.", $exception->getMessage());
    }
}
