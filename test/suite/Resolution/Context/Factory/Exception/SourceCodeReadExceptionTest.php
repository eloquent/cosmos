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

use Eloquent\Cosmos\ClassName\ClassName;
use Eloquent\Pathogen\FileSystem\FileSystemPath;
use Exception;
use PHPUnit_Framework_TestCase;

class SourceCodeReadExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $className = ClassName::fromString('\Foo');
        $path = FileSystemPath::fromString('/path/to/foo.php');
        $cause = new Exception;
        $exception = new SourceCodeReadException($className, $path, $cause);

        $this->assertSame($className, $exception->className());
        $this->assertSame($path, $exception->path());
        $this->assertSame("Unable to read the source code for class '\\\\Foo' from '/path/to/foo.php'.", $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($cause, $exception->getPrevious());
    }
}
