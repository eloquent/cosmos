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
use Eloquent\Cosmos\UseStatement\UseStatementClauseInterface;
use Eloquent\Cosmos\UseStatement\UseStatementType;

/**
 * The interface implemented by use statement factories.
 */
interface UseStatementFactoryInterface
{
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
    );

    /**
     * Create a new use statement.
     *
     * @param array<UseStatementClauseInterface> The clauses.
     * @param UseStatementType|null $type The use statement type.
     */
    public function createStatement(
        array $clauses,
        UseStatementType $type = null
    );
}
