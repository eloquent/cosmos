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
            self::$instance = new self();
        }

        return self::$instance;
    }

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
    ) {
        return $this->createStatement(
            array($this->createClause($symbol, $alias)),
            $type
        );
    }

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
    ) {
        return new UseStatementClause($symbol, $alias);
    }

    /**
     * Create a new use statement.
     *
     * @param array<UseStatementClauseInterface> The clauses.
     * @param UseStatementType|null $type The use statement type.
     *
     * @return UseStatementInterface The newly created use statement.
     */
    public function createStatement(
        // @codeCoverageIgnoreStart
        array $clauses,
        UseStatementType $type = null
        // @codeCoverageIgnoreEnd
)
    {
        return new UseStatement($clauses, $type);
    }

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
    ) {
        return $this->createStatement(array($clause), $type);
    }

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
    ) {
        if (null === $type) {
            $type = UseStatementType::TYPE();
        }

        $statements = array();
        foreach ($clauses as $clause) {
            $statements[] = $this->createStatementFromClause($clause, $type);
        }

        return $statements;
    }

    private static $instance;
}
