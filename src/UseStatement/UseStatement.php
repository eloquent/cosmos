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

use Eloquent\Cosmos\Resolution\Context\ResolutionContextVisitorInterface;
use Eloquent\Cosmos\Symbol\Exception\InvalidSymbolAtomException;
use Eloquent\Cosmos\Symbol\QualifiedSymbol;
use Eloquent\Cosmos\Symbol\QualifiedSymbolInterface;
use Eloquent\Cosmos\Symbol\SymbolReferenceInterface;

/**
 * Represents a use statement.
 */
class UseStatement implements UseStatementInterface
{
    /**
     * Construct a new use statement.
     *
     * @param QualifiedSymbolInterface      $symbol The symbol.
     * @param SymbolReferenceInterface|null $alias  The alias for the symbol.
     * @param UseStatementType|null         $type   The use statement type.
     *
     * @throws InvalidSymbolAtomException If an invalid alias is supplied.
     */
    public function __construct(
        QualifiedSymbolInterface $symbol,
        SymbolReferenceInterface $alias = null,
        UseStatementType $type = null
    ) {
        if (null === $type) {
            $type = UseStatementType::TYPE();
        }

        $this->symbol = $symbol->normalize();
        $this->setAlias($alias);
        $this->type = $type;
    }

    /**
     * Get the symbol.
     *
     * @return QualifiedSymbolInterface The symbol.
     */
    public function symbol()
    {
        return $this->symbol;
    }

    /**
     * Set the alias for the symbol.
     *
     * @param SymbolReferenceInterface|null $alias The alias, or null to remove the alias.
     */
    public function setAlias(SymbolReferenceInterface $alias = null)
    {
        if (null === $alias) {
            $this->alias = null;
        } else {
            $normalizedAlias = $alias->normalize();
            $aliasAtoms = $normalizedAlias->atoms();

            if (
                count($aliasAtoms) > 1 ||
                QualifiedSymbol::SELF_ATOM === $aliasAtoms[0] ||
                QualifiedSymbol::PARENT_ATOM === $aliasAtoms[0]
            ) {
                throw new InvalidSymbolAtomException($alias->string());
            }

            $this->alias = $normalizedAlias;
        }
    }

    /**
     * Get the alias for the symbol.
     *
     * @return SymbolReferenceInterface|null The alias, or null if no alias is in use.
     */
    public function alias()
    {
        return $this->alias;
    }

    /**
     * Get the effective alias for the symbol.
     *
     * @return SymbolReferenceInterface The alias, or the last atom of the symbol.
     */
    public function effectiveAlias()
    {
        if (null === $this->alias()) {
            return $this->symbol()->lastAtomAsReference();
        }

        return $this->alias();
    }

    /**
     * Get the use statement type.
     *
     * @return UseStatementType The type.
     */
    public function type()
    {
        return $this->type;
    }

    /**
     * Generate a string representation of this use statement.
     *
     * @return string A string representation of this use statement.
     */
    public function string()
    {
        if (null === $this->alias()) {
            return sprintf('use %s', $this->symbol()->toRelative()->string());
        }

        return sprintf(
            'use %s as %s',
            $this->symbol()->toRelative()->string(),
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

    private $symbol;
    private $alias;
    private $type;
}
