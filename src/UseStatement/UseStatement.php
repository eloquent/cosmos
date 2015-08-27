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

/**
 * Represents a use statement.
 */
class UseStatement implements UseStatementInterface
{
    /**
     * Construct a new use statement.
     *
     * @param array<UseStatementClauseInterface> The clauses.
     * @param string|null $type The type, or null for a generic statement.
     */
    public function __construct(array $clauses, $type = null)
    {
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
     * Get the type.
     *
     * @return string|null The type.
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
    public function __toString()
    {
        if (null === $this->type) {
            $string = 'use ';
        } else {
            $string = 'use ' . $this->type . ' ';
        }

        $clauses = array();

        foreach ($this->clauses as $clause) {
            $clauses[] = strval($clause);
        }

        return $string . implode(', ', $clauses);
    }

    private $clauses;
    private $type;
}
