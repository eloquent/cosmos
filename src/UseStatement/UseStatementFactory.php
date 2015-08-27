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
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Create a new use statement clause.
     *
     * @param SymbolInterface $symbol The symbol.
     * @param string|null     $alias  The alias for the symbol.
     *
     * @return UseStatementClauseInterface The newly created use statement clause.
     * @throws InvalidSymbolAtomException  If an invalid alias is supplied.
     */
    public function createClause(SymbolInterface $symbol, $alias = null)
    {
        return new UseStatementClause($symbol, $alias);
    }

    /**
     * Create a new use statement.
     *
     * @param array<UseStatementClauseInterface> $clauses The clauses.
     * @param string|null                        $type    The use statement type.
     *
     * @return UseStatementInterface The newly created use statement.
     */
    public function createStatement(array $clauses, $type = null)
    {
        return new UseStatement($clauses, $type);
    }

    private static $instance;
}
