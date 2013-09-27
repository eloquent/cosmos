<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Cosmos\ClassName\Exception;

use Exception;

/**
 * The supplied class name atoms constitute an invalid class name.
 */
final class InvalidClassNameException extends Exception
{
    /**
     * Construct a new invalid class name exception.
     *
     * @param Exception|null $previous The cause, if available.
     */
    public function __construct(Exception $previous = null)
    {
        parent::__construct('Invalid class name.', 0, $previous);
    }
}
