<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Context\Persistence\Exception;

use Exception;

/**
 * The requested resolution context is undefined.
 */
final class UndefinedResolutionContextException extends Exception
{
    /**
     * Construct a new undefined resolution context exception.
     *
     * @param integer        $index The specified index.
     * @param string|null    $path  The path, if known.
     * @param Exception|null $cause The cause, if available.
     */
    public function __construct($index, $path = null, Exception $cause = null)
    {
        $this->index = $index;
        $this->path = $path;

        if (null === $path) {
            $message = sprintf(
                'No resolution context defined at index %s.',
                var_export($index, true)
            );
        } else {
            $message = sprintf(
                'No resolution context defined at index %s in file %s.',
                var_export($index, true),
                var_export($path, true)
            );
        }

        parent::__construct($message, 0, $cause);
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
     * @return string|null The path, if known.
     */
    public function path()
    {
        return $this->path;
    }

    private $index;
    private $path;
}
