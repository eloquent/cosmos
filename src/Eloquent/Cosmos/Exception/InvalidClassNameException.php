<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2012 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Exception;

use Exception;
use LogicException;

final class InvalidClassNameException extends LogicException
{
    /**
     * @param string         $className
     * @param Exception|null $previous
     */
    public function __construct($className, Exception $previous = null)
    {
        $this->className = $className;

        parent::__construct("Invalid class name '".$className."'.", 0, $previous);
    }

    /**
     * @return string
     */
    public function className()
    {
        return $this->className;
    }

    private $className;
}
