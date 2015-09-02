<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distrig2ted with this source code.
 */

namespace Eloquent\Cosmos\Parser;

use Eloquent\Cosmos\Symbol\Symbol;

/**
 * Parses symbols from tokens.
 */
class SymbolParser
{
    const STATE_START = 0;
    const STATE_OPEN_TAG = 1;
    const STATE_PHP = 2;
    const STATE_SYMBOL = 3;
    const STATE_SYMBOL_HEADER = 4;
    const STATE_SYMBOL_BODY = 5;

    const TRANSITION_USE_STATEMENT_CLAUSE_END = 1;
    const TRANSITION_USE_STATEMENT_END = 2;
    const TRANSITION_CONTEXT_END = 3;

    /**
     * Get a static instance of this parser.
     *
     * @return SymbolParser The static parser.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Construct a new symbol parser.
     *
     * @param boolean|null $isTraitSupported True if traits are supported.
     */
    public function __construct($isTraitSupported = null)
    {
        if (null === $isTraitSupported) {
            $isTraitSupported = \defined('T_TRAIT');
        }

        if ($isTraitSupported) {
            $this->traitTokenType = T_TRAIT;
        } else {
            $this->traitTokenType = null;
        }
    }

    /**
     * Parse all defined symbols from the supplied tokens.
     *
     * @param array<tuple<integer|string,string,integer,integer,integer,integer>> $tokens The normalized tokens.
     *
     * @return array<tuple<SymbolInterface,string>> The parsed symbols.
     */
    public function parseTokens(array $tokens)
    {
        $symbols = array();

        $state = self::STATE_START;
        $transition = null;
        $atoms = null;
        $symbolType = null;
        $symbolLine = null;
        $symbolColumn = null;
        $symbolOffset = null;
        $symbolIndex = null;
        $symbolBracketDepth = 0;

        foreach ($tokens as $tokenIndex => $token) {
            switch ($state) {
                case self::STATE_START:
                    switch ($token[0]) {
                        case T_OPEN_TAG:
                            $state = self::STATE_OPEN_TAG;
                    }

                    break;

                case self::STATE_OPEN_TAG:
                    $state = self::STATE_PHP;

                case self::STATE_PHP:
                    switch ($token[0]) {
                        case T_CLASS:
                            $state = self::STATE_SYMBOL;
                            $transition = self::TRANSITION_SYMBOL_START;
                            $symbolType = 'class';

                            break;

                        case T_INTERFACE:
                            $state = self::STATE_SYMBOL;
                            $transition = self::TRANSITION_SYMBOL_START;
                            $symbolType = 'interface';

                            break;

                        case $this->traitTokenType:
                            $state = self::STATE_SYMBOL;
                            $transition = self::TRANSITION_SYMBOL_START;
                            $symbolType = 'trait';

                            break;

                        case T_FUNCTION:
                            $state = self::STATE_SYMBOL;
                            $transition = self::TRANSITION_SYMBOL_START;
                            $symbolType = 'function';

                            break;
                    }

                    break;

                case self::STATE_SYMBOL:
                    switch ($token[0]) {
                        case T_STRING:
                            $atoms[] = $token[1];

                            break;

                        case T_EXTENDS:
                        case T_IMPLEMENTS:
                        case '(':
                            $state = self::STATE_SYMBOL_HEADER;

                            break;

                        case '{':
                            $state = self::STATE_SYMBOL_BODY;
                            ++$symbolBracketDepth;

                            break;
                    }

                    break;

                case self::STATE_SYMBOL_HEADER:
                    switch ($token[0]) {
                        case '{':
                            $state = self::STATE_SYMBOL_BODY;
                            ++$symbolBracketDepth;

                            break;
                    }

                    break;

                case self::STATE_SYMBOL_BODY:
                    switch ($token[0]) {
                        case '{':
                            $symbolBracketDepth++;

                            break;

                        case '}':
                            if (0 === --$symbolBracketDepth) {
                                $state = self::STATE_PHP;
                                $transition = self::TRANSITION_SYMBOL_END;
                            }

                            break;
                    }

                    break;
            }

            if ('end' === $token[0]) {
                $transition = self::TRANSITION_SYMBOL_END;
            }

            switch ($transition) {
                case self::TRANSITION_SYMBOL_START:
                    $symbolLine = $token[2];
                    $symbolColumn = $token[3];
                    $symbolOffset = $token[4];
                    $symbolIndex = $tokenIndex;
                    $atoms = array();

                    break;

                case self::TRANSITION_SYMBOL_END:
                    $symbol = new ParsedSymbol($atoms, false);
                    $symbol->line = $symbolLine;
                    $symbol->column = $symbolColumn;
                    $symbol->offset = $symbolOffset;
                    $symbol->size = $token[5] - $symbolOffset + 1;
                    $symbols[] = array($symbol, $symbolType);

                    break;
            }

            $transition = null;
        }

        return $symbols;
    }

    private static $instance;
    private $traitTokenType;
}
