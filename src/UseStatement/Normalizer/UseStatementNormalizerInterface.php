<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\UseStatement\Normalizer;

use Eloquent\Cosmos\UseStatement\UseStatementClauseInterface;
use Eloquent\Cosmos\UseStatement\UseStatementInterface;

/**
 * The interface implemented by use statement normalizers.
 */
interface UseStatementNormalizerInterface
{
    /**
     * Normalize the supplied use statements.
     *
     * @param array<UseStatementInterface> $useStatements The use statements to normalize.
     *
     * @return array<UseStatementInterface> The normalized use statements.
     */
    public function normalize(array $useStatements);

    /**
     * Normalize the supplied use statement clauses.
     *
     * @param array<UseStatementClauseInterface> $useStatementClauses The use statement clauses to normalize.
     *
     * @return array<UseStatementClauseInterface> The normalized use statement clauses.
     */
    public function normalizeClauses(array $useStatementClauses);
}
