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

use Eloquent\Cosmos\Symbol\Exception\InvalidSymbolAtomException;
use Eloquent\Cosmos\Symbol\QualifiedSymbolInterface;
use Eloquent\Cosmos\Symbol\SymbolReferenceInterface;
use Eloquent\Cosmos\UseStatement\UseStatement;
use Eloquent\Cosmos\UseStatement\UseStatementClause;
use Eloquent\Cosmos\UseStatement\UseStatementClauseInterface;
use Eloquent\Cosmos\UseStatement\UseStatementType;

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
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Create a new use statement clause.
     *
     * @param QualifiedSymbolInterface      $symbol The symbol.
     * @param SymbolReferenceInterface|null $alias  The alias for the symbol.
     *
     * @throws InvalidSymbolAtomException If an invalid alias is supplied.
     */
    public function createClause(
        QualifiedSymbolInterface $symbol,
        SymbolReferenceInterface $alias = null
    ) {
        return new UseStatementClause($symbol, $alias);
    }

    /**
     * Create a new use statement.
     *
     * @param array<UseStatementClauseInterface> The clauses.
     * @param UseStatementType|null $type The use statement type.
     */
    public function createStatement(
        array $clauses,
        UseStatementType $type = null
    ) {
        return new UseStatement($clauses, $type);
    }

    private static $instance;
}
