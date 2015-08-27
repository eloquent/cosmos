<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2015 Erin Millard
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
 * Represents a use statement clause.
 */
class UseStatementClause implements UseStatementClauseInterface
{
    /**
     * Construct a new use statement clause.
     *
     * @param QualifiedSymbolInterface      $symbol The symbol.
     * @param SymbolReferenceInterface|null $alias  The alias for the symbol.
     *
     * @throws InvalidSymbolAtomException If an invalid alias is supplied.
     */
    public function __construct(
        QualifiedSymbolInterface $symbol,
        SymbolReferenceInterface $alias = null
    ) {
        $this->symbol = $symbol->normalize();
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
     * Get the symbol.
     *
     * @return QualifiedSymbolInterface The symbol.
     */
    public function symbol()
    {
        return $this->symbol;
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
     * Generate a string representation of this use statement clause.
     *
     * @return string A string representation of this use statement clause.
     */
    public function string()
    {
        if (null === $this->alias()) {
            return $this->symbol()->toRelative()->string();
        }

        return sprintf(
            '%s as %s',
            $this->symbol()->toRelative()->string(),
            $this->alias()->string()
        );
    }

    /**
     * Generate a string representation of this use statement clause.
     *
     * @return string A string representation of this use statement clause.
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
        return $visitor->visitUseStatementClause($this);
    }

    private $symbol;
    private $alias;
}
