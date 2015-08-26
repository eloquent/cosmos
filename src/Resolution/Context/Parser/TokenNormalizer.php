<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2015 Erin Millard
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
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Normalize the supplied PHP tokens.
     *
     * This method ensures all tokens include type, content, line number, column
     * number, offset, and end offset information.
     *
     * @param array<integer,tuple<integer,string,integer>|string> $tokens The tokens as returned by token_get_all().
     *
     * @return array<tuple<integer|string,string,integer,integer,integer,integer>> The normalized tokens.
     */
    public function normalizeTokens(array $tokens)
    {
        $lineNumber = 1;
        $columnNumber = 1;
        $startOffset = 0;
        $endOffset = 0;
        foreach ($tokens as $index => $token) {
            if (is_string($token)) {
                $endOffset = $startOffset + strlen($token) - 1;

                $tokens[$index] = array(
                    $token,
                    $token,
                    $lineNumber,
                    $columnNumber,
                    $startOffset,
                    $endOffset,
                );

                ++$columnNumber;
            } else {
                $endOffset = $startOffset + strlen($token[1]) - 1;

                $tokens[$index][] = $columnNumber;
                $tokens[$index][] = $startOffset;
                $tokens[$index][] = $endOffset;

                $lines = preg_split('/(?:\r|\n|\r\n)/', $token[1]);
                $numNewlines = count($lines) - 1;

                if ($numNewlines > 0) {
                    $lineNumber += $numNewlines;
                    $columnNumber = strlen($lines[$numNewlines]) + 1;
                } else {
                    $columnNumber += strlen($token[1]);
                }
            }

            $startOffset = $endOffset + 1;
        }

        $tokens[] = array(
            'end',
            '',
            $lineNumber,
            $columnNumber,
            $startOffset,
            $startOffset,
        );

        return $tokens;
    }

    private static $instance;
}
