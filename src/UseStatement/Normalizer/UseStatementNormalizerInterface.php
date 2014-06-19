<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\UseStatement\Normalizer;

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
}
