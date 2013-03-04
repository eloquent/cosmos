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

final class NamespaceMismatchException extends LogicException
{
    /**
     * @param ClassName      $className
     * @param ClassName      $namespaceName
     * @param Exception|null $previous
     */
    public function __construct(
        ClassName $className,
        ClassName $namespaceName,
        Exception $previous = null
    ) {
        $this->className = $className;
        $this->namespaceName = $namespaceName;

        parent::__construct(
            sprintf(
                "Class '%s' does not belong to namespace '%s'.",
                $className->string(),
                $namespaceName->string()
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

    /**
     * @return ClassName
     */
    public function namespaceName()
    {
        return $this->namespaceName;
    }

    private $className;
    private $namespaceName;
}
