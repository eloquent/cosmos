<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\UseStatement\Exception;

use Exception;

/**
 * Use statements must have at least one clause.
 */
final class EmptyUseStatementException extends Exception
{
    /**
     * Construct a new empty use statement exception.
     *
     * @param Exception|null $cause The cause, if available.
     */
    public function __construct(Exception $cause = null)
    {
        parent::__construct(
            'Use statements must have at least one clause.',
            0,
            $cause
        );
    }
}
