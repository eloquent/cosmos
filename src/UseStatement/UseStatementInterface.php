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

use Eloquent\Cosmos\Resolution\Context\ResolutionContextElementInterface;

/**
 * The interface implemented by use statements.
 */
interface UseStatementInterface extends ResolutionContextElementInterface
{
    /**
     * Get the clauses.
     *
     * @return array<UseStatementClauseInterface> The clauses.
     */
    public function clauses();

    /**
     * Get the use statement type.
     *
     * @return UseStatementType The type.
     */
    public function type();

    /**
     * Generate a string representation of this use statement.
     *
     * @return string A string representation of this use statement.
     */
    public function string();

    /**
     * Generate a string representation of this use statement.
     *
     * @return string A string representation of this use statement.
     */
    public function __toString();
}
