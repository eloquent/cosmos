<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Context\Factory\Exception;

use Eloquent\Pathogen\FileSystem\FileSystemPath;
use Exception;
use PHPUnit_Framework_TestCase;

class UndefinedResolutionContextExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $path = FileSystemPath::fromString('/path/to/foo.php');
        $cause = new Exception;
        $exception = new UndefinedResolutionContextException(111, $path, $cause);

        $this->assertSame(111, $exception->index());
        $this->assertSame($path, $exception->path());
        $this->assertSame("No resolution context defined at index 111 in file '/path/to/foo.php'.", $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($cause, $exception->getPrevious());
    }

    public function testExceptionDefaults()
    {
        $exception = new UndefinedResolutionContextException(111);

        $this->assertNull($exception->path());
        $this->assertSame("No resolution context defined at index 111.", $exception->getMessage());
    }
}