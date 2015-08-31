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
use InvalidArgumentException;

/**
 * Represents a use statement.
 *
 * @api
 */
class UseStatement implements UseStatementInterface
{
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
    public static function fromSymbol(
        SymbolInterface $symbol,
        $alias = null,
        $type = null
    ) {
        return UseStatementFactory::instance()
            ->createStatementFromSymbol($symbol, $alias, $type);
    }

    /**
     * Construct a new use statement.
     *
     * @param array<UseStatementClauseInterface> The clauses.
     * @param string|null $type The type, or null for a generic statement.
     */
    public function __construct(array $clauses, $type = null)
    {
        if (\count($clauses) < 1) {
            throw new InvalidArgumentException(
                'Use statements cannot be empty.'
            );
        }

        $this->clauses = $clauses;
        $this->type = $type;
    }

    /**
     * Get the clauses.
     *
     * @api
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
     * @api
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
     * @api
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
            $clauses[] = \strval($clause);
        }

        return $string . \implode(', ', $clauses);
    }

    private $clauses;
    private $type;
}
