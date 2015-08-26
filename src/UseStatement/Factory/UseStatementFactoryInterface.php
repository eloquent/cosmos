<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2015 Erin Millard
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
     * Create a new use statement with a single clause.
     *
     * @param QualifiedSymbolInterface      $symbol The symbol.
     * @param SymbolReferenceInterface|null $alias  The alias for the symbol.
     * @param UseStatementType|null         $type   The use statement type.
     *
     * @return UseStatementInterface      The newly created use statement.
     * @throws InvalidSymbolAtomException If an invalid alias is supplied.
     */
    public function create(
        QualifiedSymbolInterface $symbol,
        SymbolReferenceInterface $alias = null,
        UseStatementType $type = null
    );

    /**
     * Create a new use statement clause.
     *
     * @param QualifiedSymbolInterface      $symbol The symbol.
     * @param SymbolReferenceInterface|null $alias  The alias for the symbol.
     *
     * @return UseStatementClauseInterface The newly created use statement clause.
     * @throws InvalidSymbolAtomException  If an invalid alias is supplied.
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
     *
     * @return UseStatementInterface The newly created use statement.
     */
    public function createStatement(
        array $clauses,
        UseStatementType $type = null
    );

    /**
     * Create a use statement from the supplied use statement clause.
     *
     * @param UseStatementClauseInterface $clause The clause.
     * @param UseStatementType|null       $type   The use statement type.
     *
     * @return UseStatementInterface The newly created use statement.
     */
    public function createStatementFromClause(
        UseStatementClauseInterface $clause,
        UseStatementType $type = null
    );

    /**
     * Create a list of use statements from the supplied use statement clauses,
     * producing one statement per clause.
     *
     * @param array<UseStatementClauseInterface> $clauses The clauses.
     * @param UseStatementType|null              $type    The use statement type.
     *
     * @return array<UseStatementInterface> The newly created use statements.
     */
    public function createStatementsFromClauses(
        array $clauses,
        UseStatementType $type = null
    );
}
