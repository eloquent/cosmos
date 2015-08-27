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

use Eloquent\Cosmos\Symbol\SymbolInterface;

/**
 * Represents a use statement clause.
 */
class UseStatementClause implements UseStatementClauseInterface
{
    /**
     * Construct a new use statement clause.
     *
     * @param SymbolInterface $symbol The symbol.
     * @param string|null     $alias  The alias for the symbol.
     */
    public function __construct(SymbolInterface $symbol, $alias = null)
    {
        $this->symbol = $symbol;
        $this->alias = $alias;

        if (null === $alias) {
            $atoms = $symbol->atoms();
            $this->effectiveAlias = array_pop($atoms);
        } else {
            $this->effectiveAlias = $alias;
        }
    }

    /**
     * Get the symbol.
     *
     * @return SymbolInterface The symbol.
     */
    public function symbol()
    {
        return $this->symbol;
    }

    /**
     * Get the alias.
     *
     * @return string|null The alias, or null if no alias is in use.
     */
    public function alias()
    {
        return $this->alias;
    }

    /**
     * Get the effective alias.
     *
     * @return string The alias, or the last atom of the symbol.
     */
    public function effectiveAlias()
    {
        return $this->effectiveAlias;
    }

    /**
     * Get the string representation of this use statement clause.
     *
     * @return string The string representation.
     */
    public function __toString()
    {
        if (null === $this->alias) {
            return $this->symbol->runtimeString();
        }

        return $this->symbol->runtimeString() . ' as ' . $this->alias;
    }

    private $symbol;
    private $alias;
    private $effectiveAlias;
}
