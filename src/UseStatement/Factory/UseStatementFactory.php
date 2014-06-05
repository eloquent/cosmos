<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\UseStatement\Factory;

use Eloquent\Cosmos\ClassName\ClassNameReferenceInterface;
use Eloquent\Cosmos\ClassName\Exception\InvalidClassNameAtomException;
use Eloquent\Cosmos\ClassName\QualifiedClassNameInterface;
use Eloquent\Cosmos\UseStatement\UseStatement;

/**
 * Creates use statement instances.
 */
class UseStatementFactory implements UseStatementFactoryInterface
{
    /**
     * Get a static instance of this factory.
     *
     * @return UseStatementFactoryInterface The static factory.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Create a new use statement.
     *
     * @param QualifiedClassNameInterface      $className The class name.
     * @param ClassNameReferenceInterface|null $alias     The alias for the class name.
     *
     * @throws InvalidClassNameAtomException If an invalid alias is supplied.
     */
    public function create(
        QualifiedClassNameInterface $className,
        ClassNameReferenceInterface $alias = null
    ) {
        return new UseStatement($className, $alias);
    }

    private static $instance;
}
