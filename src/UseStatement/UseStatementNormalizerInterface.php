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
 * The interface implemented by use statement normalizers.
 *
 * @api
 */
interface UseStatementNormalizerInterface
{
    /**
     * Normalize the supplied use statements.
     *
     * @api
     *
     * @param array<UseStatementInterface> $statements The use statements to normalize.
     *
     * @return array<UseStatementInterface> The normalized use statements.
     */
    public function normalizeStatements(array $statements);
}
