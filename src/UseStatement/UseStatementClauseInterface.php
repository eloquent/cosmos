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
 * The interface implemented by use statement clauses.
 */
interface UseStatementClauseInterface
{
    /**
     * Get the symbol.
     *
     * @return SymbolInterface The symbol.
     */
    public function symbol();

    /**
     * Get the alias.
     *
     * @return string|null The alias, or null if no alias is in use.
     */
    public function alias();

    /**
     * Get the effective alias.
     *
     * @return string The alias, or the last atom of the symbol.
     */
    public function effectiveAlias();

    /**
     * Get the string representation of this use statement clause.
     *
     * @return string The string representation.
     */
    public function __toString();
}
