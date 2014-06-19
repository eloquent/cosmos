<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\UseStatement;

use Eloquent\Cosmos\Resolution\Context\ResolutionContextVisitorInterface;
use Eloquent\Cosmos\Symbol\Exception\InvalidSymbolAtomException;
use Eloquent\Cosmos\Symbol\QualifiedSymbolInterface;
use Eloquent\Cosmos\Symbol\SymbolReferenceInterface;
use Eloquent\Cosmos\UseStatement\Exception\EmptyUseStatementException;
use Eloquent\Cosmos\UseStatement\Factory\UseStatementFactory;
use Eloquent\Cosmos\UseStatement\Factory\UseStatementFactoryInterface;

/**
 * Represents a use statement.
 */
class UseStatement implements UseStatementInterface
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
    public static function create(
        QualifiedSymbolInterface $symbol,
        SymbolReferenceInterface $alias = null,
        UseStatementType $type = null
    ) {
        return static::factory()->create($symbol, $alias, $type);
    }

    /**
     * Create a use statement from the supplied use statement clause.
     *
     * @param UseStatementClauseInterface $clause The clause.
     * @param UseStatementType|null       $type   The use statement type.
     *
     * @return UseStatementInterface The newly created use statement.
     */
    public static function fromClause(
        UseStatementClauseInterface $clause,
        UseStatementType $type = null
    ) {
        return static::factory()->createStatementFromClause($clause, $type);
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
    public static function fromClauses(
        array $clauses,
        UseStatementType $type = null
    ) {
        return static::factory()->createStatementsFromClauses($clauses, $type);
    }

    /**
     * Construct a new use statement.
     *
     * @param array<UseStatementClauseInterface> The clauses.
     * @param UseStatementType|null $type The use statement type.
     *
     * @throws EmptyUseStatementException If no clauses are supplied.
     */
    public function __construct(array $clauses, UseStatementType $type = null)
    {
        if (null === $type) {
            $type = UseStatementType::TYPE();
        }

        if (count($clauses) < 1) {
            throw new EmptyUseStatementException;
        }

        $this->clauses = $clauses;
        $this->type = $type;
    }

    /**
     * Get the clauses.
     *
     * @return array<UseStatementClauseInterface> The clauses.
     */
    public function clauses()
    {
        return $this->clauses;
    }

    /**
     * Get the use statement type.
     *
     * @return UseStatementType The type.
     */
    public function type()
    {
        return $this->type;
    }

    /**
     * Generate a string representation of this use statement.
     *
     * @return string A string representation of this use statement.
     */
    public function string()
    {
        $string = 'use ';
        if (UseStatementType::TYPE() !== $this->type()) {
            $string .= $this->type()->value() . ' ';
        }

        $clauses = array();
        foreach ($this->clauses() as $clause) {
            $clauses[] = $clause->string();
        }

        return $string . implode(', ', $clauses);
    }

    /**
     * Generate a string representation of this use statement.
     *
     * @return string A string representation of this use statement.
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
        return $visitor->visitUseStatement($this);
    }

    /**
     * Get the use statement factory.
     *
     * @return UseStatementFactoryInterface The use statement factory.
     */
    protected static function factory()
    {
        return UseStatementFactory::instance();
    }

    private $clauses;
    private $type;
}
