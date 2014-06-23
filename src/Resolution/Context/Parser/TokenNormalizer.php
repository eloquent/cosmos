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
 * Normalizes lists of PHP tokens.
 */
class TokenNormalizer implements TokenNormalizerInterface
{
    /**
     * Get a static instance of this normalizer.
     *
     * @return TokenNormalizerInterface The static normalizer.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

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
    public function normalizeTokens(array $tokens)
    {
        $lineNumber = 1;
        $columnNumber = 1;
        foreach ($tokens as $index => $token) {
            if (is_string($token)) {
                $tokens[$index] = array(
                    $token,
                    $token,
                    $lineNumber,
                    $columnNumber
                );
                $columnNumber++;
            } else {
                $tokens[$index][] = $columnNumber;

                $lines = preg_split('/(?:\r|\n|\r\n)/', $token[1]);
                $numNewlines = count($lines) - 1;

                if ($numNewlines > 0) {
                    $lineNumber += $numNewlines;
                    $columnNumber = strlen($lines[$numNewlines]) + 1;
                } else {
                    $columnNumber += strlen($token[1]);
                }
            }
        }

        $tokens[] = array('end', '', $lineNumber, $columnNumber);

        return $tokens;
    }

    private static $instance;
}
