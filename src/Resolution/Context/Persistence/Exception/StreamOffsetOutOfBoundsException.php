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
 * The requested stream offset is out of bounds.
 */
final class StreamOffsetOutOfBoundsException extends Exception
{
    /**
     * Construct a new stream offset out of bounds exception.
     *
     * @param integer        $offset The specified offset.
     * @param string|null    $path   The path, if known.
     * @param Exception|null $cause  The cause, if available.
     */
    public function __construct($offset, $path = null, Exception $cause = null)
    {
        $this->offset = $offset;
        $this->path = $path;

        if (null === $path) {
            $message = sprintf(
                'Stream offset %d is out of bounds.',
                var_export($offset, true)
            );
        } else {
            $message = sprintf(
                'Stream offset %d is out of bounds in file %s.',
                var_export($offset, true),
                var_export($path, true)
            );
        }

        parent::__construct($message, 0, $cause);
    }

    /**
     * Get the specified offset.
     *
     * @return interger The offset.
     */
    public function offset()
    {
        return $this->offset;
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

    private $offset;
    private $path;
}
