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

use Eloquent\Cosmos\ClassName;
use Exception;
use LogicException;

final class ParentException extends LogicException
{
    /**
     * @param ClassName      $className
     * @param Exception|null $previous
     */
    public function __construct(ClassName $className, Exception $previous = null)
    {
        $this->className = $className;

        parent::__construct(
            sprintf(
                "Unable to determine parent for class '%s'.",
                $className->string()
            ),
            0,
            $previous
        );
    }

    /**
     * @return ClassName
     */
    public function className()
    {
        return $this->className;
    }

    private $className;
}
