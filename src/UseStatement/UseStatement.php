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
use Eloquent\Cosmos\ClassName\Exception\InvalidClassNameAtomException;
use Eloquent\Cosmos\ClassName\QualifiedClassName;
use Eloquent\Cosmos\ClassName\QualifiedClassNameInterface;
use Eloquent\Cosmos\Resolution\Context\ResolutionContextVisitorInterface;

/**
 * Represents a use statement.
 */
class UseStatement implements UseStatementInterface
{
    /**
     * Construct a new use statement.
     *
     * @param QualifiedClassNameInterface      $className The class name.
     * @param ClassNameReferenceInterface|null $alias     The alias for the class name.
     *
     * @throws InvalidClassNameAtomException If an invalid alias is supplied.
     */
    public function __construct(
        QualifiedClassNameInterface $className,
        ClassNameReferenceInterface $alias = null
    ) {
        $this->className = $className->normalize();
        $this->setAlias($alias);
    }

    /**
     * Get the class name.
     *
     * @return QualifiedClassNameInterface The class name.
     */
    public function className()
    {
        return $this->className;
    }

    /**
     * Set the alias for the class name.
     *
     * @param ClassNameReferenceInterface|null $alias The alias, or null to remove the alias.
     */
    public function setAlias(ClassNameReferenceInterface $alias = null)
    {
        if (null === $alias) {
            $this->alias = null;
        } else {
            $normalizedAlias = $alias->normalize();
            $aliasAtoms = $normalizedAlias->atoms();

            if (
                count($aliasAtoms) > 1 ||
                QualifiedClassName::SELF_ATOM === $aliasAtoms[0] ||
                QualifiedClassName::PARENT_ATOM === $aliasAtoms[0]
            ) {
                throw new InvalidClassNameAtomException($alias->string());
            }

            $this->alias = $normalizedAlias;
        }
    }

    /**
     * Get the alias for the class name.
     *
     * @return ClassNameReferenceInterface|null The alias, or null if no alias is in use.
     */
    public function alias()
    {
        return $this->alias;
    }

    /**
     * Get the effective alias for the class name.
     *
     * @return ClassNameReferenceInterface The alias, or the last atom of the class name.
     */
    public function effectiveAlias()
    {
        if (null === $this->alias()) {
            return $this->className()->shortName();
        }

        return $this->alias();
    }

    /**
     * Generate a string representation of this use statement.
     *
     * @return string A string representation of this use statement.
     */
    public function string()
    {
        if (null === $this->alias()) {
            return sprintf(
                'use %s',
                $this->className()->toRelative()->string()
            );
        }

        return sprintf(
            'use %s as %s',
            $this->className()->toRelative()->string(),
            $this->alias()->string()
        );
    }

    /**
     * Generate a string representation of this use statement.
     *
     * @return string A string representation of this use statement.
     */
    public function __toString()
    {
        return $this->string();
    }

    /**
     * Accept a visitor.
     *
     * @param ResolutionContextVisitorInterface $visitor The visitor to accept.
     *
     * @return mixed The result of visitation.
     */
    public function accept(ResolutionContextVisitorInterface $visitor)
    {
        return $visitor->visitUseStatement($this);
    }

    private $className;
    private $alias;
}
