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

/**
 * The interface implemented by use statement factories.
 */
interface UseStatementFactoryInterface
{
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
    );
}
