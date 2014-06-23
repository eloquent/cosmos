<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Context\Parser;

/**
 * The interface implemented by PHP token normalizers.
 */
interface TokenNormalizerInterface
{
    /**
     * Normalize the supplied PHP tokens.
     *
     * This method ensures all tokens include type, content, line number, and
     * column number information.
     *
     * @param array<integer,tuple<integer,string,integer>|string> $tokens The tokens as returned by token_get_all().
     *
     * @return array<tuple<integer|string,string,integer,integer>> The normalized tokens.
     */
    public function normalizeTokens(array $tokens);
}
