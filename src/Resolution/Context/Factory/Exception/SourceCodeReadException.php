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

use Eloquent\Cosmos\ClassName\ClassNameInterface;
use Eloquent\Pathogen\FileSystem\FileSystemPathInterface;
use Exception;

/**
 * The source code could not be read from the file system.
 */
final class SourceCodeReadException extends Exception
{
    /**
     * Construct a new source code read exception.
     *
     * @param ClassNameInterface      $className The class name.
     * @param FileSystemPathInterface $path      The path.
     * @param Exception|null          $cause     The cause, if available.
     */
    public function __construct(
        ClassNameInterface $className,
        FileSystemPathInterface $path,
        Exception $cause = null
    ) {
        $this->className = $className;
        $this->path = $path;

        parent::__construct(
            sprintf(
                'Unable to read the source code for class %s from %s.',
                var_export($className->string(), true),
                var_export($path->string(), true)
            ),
            0,
            $cause
        );
    }

    /**
     * Get the class name.
     *
     * @return ClassNameInterface The class name.
     */
    public function className()
    {
        return $this->className;
    }

    /**
     * Get the path.
     *
     * @return FileSystemPathInterface The path.
     */
    public function path()
    {
        return $this->path;
    }

    private $className;
    private $path;
}
