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

use Eloquent\Cosmos\Resolution\Context\ResolutionContextElementInterface;
use Eloquent\Cosmos\Symbol\QualifiedSymbolInterface;
use Eloquent\Cosmos\Symbol\SymbolReferenceInterface;

/**
 * The interface implemented by use statement clauses.
 */
interface UseStatementClauseInterface extends ResolutionContextElementInterface
{
    /**
     * Get the symbol.
     *
     * @return QualifiedSymbolInterface The symbol.
     */
    public function symbol();

    /**
     * Get the alias for the symbol.
     *
     * @return SymbolReferenceInterface|null The alias, or null if no alias is in use.
     */
    public function alias();

    /**
     * Get the effective alias for the symbol.
     *
     * @return SymbolReferenceInterface The alias, or the last atom of the symbol.
     */
    public function effectiveAlias();

    /**
     * Generate a string representation of this use statement clause.
     *
     * @return string A string representation of this use statement clause.
     */
    public function string();

    /**
     * Generate a string representation of this use statement clause.
     *
     * @return string A string representation of this use statement clause.
     */
    public function __toString();
}
