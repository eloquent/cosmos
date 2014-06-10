<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Exception;

use Eloquent\Cosmos\ClassName\ClassNameInterface;
use Exception;

/**
 * The specified class is undefined.
 */
final class UndefinedClassException extends Exception
{
    /**
     * Construct a new undefined class exception.
     *
     * @param ClassNameInterface $className The class name.
     * @param Exception|null     $cause     The cause, if available.
     */
    public function __construct(
        ClassNameInterface $className,
        Exception $cause = null
    ) {
        $this->className = $className;

        parent::__construct(
            sprintf(
                'Undefined class %s.',
                var_export($className->string(), true)
            ),
            0,
            $cause
        );
    }

    /**
     * Get the class name.
     *
     * @return ClassNameInterface The class name.
     */
    public function className()
    {
        return $this->className;
    }

    private $className;
}
