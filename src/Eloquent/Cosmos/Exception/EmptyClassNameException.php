<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Exception;

use Exception;
use LogicException;

final class EmptyClassNameException extends LogicException
{
    /**
     * @param Exception|null $previous
     */
    public function __construct(Exception $previous = null)
    {
        parent::__construct(
            'Class names cannot be empty.',
            0,
            $previous
        );
    }
}
