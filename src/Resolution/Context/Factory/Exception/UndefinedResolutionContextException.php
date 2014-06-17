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

use Eloquent\Pathogen\FileSystem\FileSystemPathInterface;
use Exception;

/**
 * The requested resolution context is undefined.
 */
final class UndefinedResolutionContextException extends Exception
{
    /**
     * Construct a new undefined resolution context exception.
     *
     * @param FileSystemPathInterface $path  The path.
     * @param integer                 $index The specified index.
     * @param Exception|null          $cause The cause, if available.
     */
    public function __construct(
        FileSystemPathInterface $path,
        $index,
        Exception $cause = null
    ) {
        $this->index = $index;
        $this->path = $path;

        parent::__construct(
            sprintf(
                'No resolution context defined at index %s in file %s.',
                var_export($index, true),
                var_export($path->string(), true)
            ),
            0,
            $cause
        );
    }

    /**
     * Get the specified index.
     *
     * @return interger The index.
     */
    public function index()
    {
        return $this->index;
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

    private $index;
    private $path;
}
