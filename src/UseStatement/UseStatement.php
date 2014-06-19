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
use Eloquent\Cosmos\UseStatement\Exception\EmptyUseStatementException;

/**
 * Represents a use statement.
 */
class UseStatement implements UseStatementInterface
{
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

    private $clauses;
    private $type;
}
