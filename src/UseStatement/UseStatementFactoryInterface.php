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

use Eloquent\Cosmos\Exception\InvalidSymbolAtomException;
use Eloquent\Cosmos\Symbol\SymbolInterface;

/**
 * The interface implemented by use statement factories.
 *
 * @api
 */
interface UseStatementFactoryInterface
{
    /**
     * Create a new use statement clause.
     *
     * @api
     *
     * @param SymbolInterface $symbol The symbol.
     * @param string|null     $alias  The alias for the symbol.
     *
     * @return UseStatementClauseInterface The newly created use statement clause.
     * @throws InvalidSymbolAtomException  If an invalid alias is supplied.
     */
    public function createClause(SymbolInterface $symbol, $alias = null);

    /**
     * Create a new use statement.
     *
     * @api
     *
     * @param array<UseStatementClauseInterface> $clauses The clauses.
     * @param string|null                        $type    The use statement type.
     *
     * @return UseStatementInterface The newly created use statement.
     */
    public function createStatement(array $clauses, $type = null);

    /**
     * Create a new use statement from a symbol.
     *
     * @api
     *
     * @param SymbolInterface $symbol The symbol.
     * @param string|null     $alias  The alias.
     * @param string|null     $type   The type.
     *
     * @return UseStatementInterface      The newly created use statement.
     * @throws InvalidSymbolAtomException If an invalid alias is supplied.
     */
    public function createStatementFromSymbol(
        SymbolInterface $symbol,
        $alias = null,
        $type = null
    );
}
