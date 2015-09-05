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

/**
 * Unable to write to a file or stream.
 *
 * @api
 */
final class WriteException extends Exception implements IoExceptionInterface
{
    /**
     * Construct a new write exception.
     *
     * @param string|null    $path  The path, if known.
     * @param Exception|null $cause The cause, if available.
     */
    public function __construct($path = null, Exception $cause = null)
    {
        $this->path = $path;

        if (null === $path) {
            $message = 'Unable to write to stream.';
        } else {
            $message = \sprintf(
                'Unable to write to %s.',
                \var_export($path, true)
            );
        }

        parent::__construct($message, 0, $cause);
    }

    /**
     * Get the path.
     *
     * @api
     *
     * @return string|null The path, if known.
     */
    public function path()
    {
        return $this->path;
    }

    private $path;
}
