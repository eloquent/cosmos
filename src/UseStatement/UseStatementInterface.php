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
 * The interface implemented by use statements.
 */
interface UseStatementInterface
{
    /**
     * Get the type.
     *
     * @return string|null The type.
     */
    public function type();

    /**
     * Get the clauses.
     *
     * @return array<UseStatementClauseInterface> The clauses.
     */
    public function clauses();

    /**
     * Get the string representation of this use statement.
     *
     * @return string The string representation.
     */
    public function __toString();
}
