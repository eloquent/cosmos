<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\UseStatement;

use Eloquent\Cosmos\ClassName\ClassNameReferenceInterface;
use Eloquent\Cosmos\ClassName\QualifiedClassNameInterface;

/**
 * The interface implemented by use statements.
 */
interface UseStatementInterface
{
    /**
     * Get the class name.
     *
     * @return QualifiedClassNameInterface The class name.
     */
    public function className();

    /**
     * Set the alias for the class name.
     *
     * @param ClassNameReferenceInterface|null $alias The alias, or null to remove the alias.
     */
    public function setAlias(ClassNameReferenceInterface $alias = null);

    /**
     * Get the alias for the class name.
     *
     * @return ClassNameReferenceInterface|null The alias, or null if no alias is in use.
     */
    public function alias();

    /**
     * Get the effective alias for the class name.
     *
     * @return ClassNameReferenceInterface The alias, or the last atom of the class name.
     */
    public function effectiveAlias();

    /**
     * Generate a string representation of this use statement.
     *
     * @return string A string representation of this use statement.
     */
    public function string();

    /**
     * Generate a string representation of this use statement.
     *
     * @return string A string representation of this use statement.
     */
    public function __toString();
}
