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

use Eloquent\Cosmos\Parser\Element\ParsedResolutionContext;
use Eloquent\Cosmos\Parser\Element\ParsedSymbol;
use Eloquent\Cosmos\Parser\Element\ParsedUseStatement;
use Eloquent\Cosmos\Symbol\Symbol;
use Eloquent\Cosmos\UseStatement\UseStatementClause;

/**
 * Parses resolution contexts from tokens.
 */
class ResolutionContextParser
{
    const STATE_START = 0;
    const STATE_OPEN_TAG = 1;
    const STATE_PHP = 2;
    const STATE_POTENTIAL_NAMESPACE_NAME = 3;
    const STATE_NAMESPACE_NAME = 4;
    const STATE_USE_STATEMENT = 5;
    const STATE_USE_STATEMENT_TYPE_NAME = 6;
    const STATE_USE_STATEMENT_ALIAS = 7;
    const STATE_SYMBOL = 8;
    const STATE_SYMBOL_HEADER = 9;
    const STATE_SYMBOL_BODY = 10;

    const TRANSITION_USE_STATEMENT_CLAUSE_END = 1;
    const TRANSITION_USE_STATEMENT_END = 2;
    const TRANSITION_CONTEXT_END = 3;
    const TRANSITION_SYMBOL_START = 4;
    const TRANSITION_SYMBOL_END = 5;

    /**
     * Get a static instance of this parser.
     *
     * @return ResolutionContextParser The static parser.
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Construct a new resolution context parser.
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
            $this->traitTokenType = null; // @codeCoverageIgnore
        }
    }

    /**
     * Parse all resolution contexts from the supplied tokens.
     *
     * @param array<tuple<integer|string,string,integer,integer,integer,integer>> $tokens The normalized tokens.
     *
     * @return array<ParsedResolutionContext> The parsed resolution contexts.
     */
    public function parseContexts(array $tokens)
    {
        $contexts = array();
        $symbols = array();

        $state = self::STATE_START;
        $transitions = array();
        $isEnd = false;
        $contextStack = array(array(false, 0, 0, 0, 0, 0, 0));
        $contextStackSize = 1;
        $namespaceAtoms = array();
        $namespaceName = null;
        $useStatementAtoms = array();
        $useStatementType = null;
        $useStatementLine = null;
        $useStatementColumn = null;
        $useStatementOffset = null;
        $useStatementIndex = null;
        $useStatementAlias = null;
        $useStatementClauses = array();
        $useStatements = array();
        $symbolAtoms = array();
        $symbolType = null;
        $symbolLine = null;
        $symbolColumn = null;
        $symbolOffset = null;
        $symbolIndex = null;
        $bracketDepth = 0;

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

                    $contextStack = array(
                        array(
                            false,
                            $token[2],
                            $token[3],
                            $token[4],
                            $token[4],
                            $tokenIndex,
                            $tokenIndex,
                        ),
                    );
                    $contextStackSize = 1;

                case self::STATE_PHP:
                    switch ($token[0]) {
                        case T_NAMESPACE:
                            $state = self::STATE_POTENTIAL_NAMESPACE_NAME;

                            \array_push(
                                $contextStack, // @codeCoverageIgnore
                                array(
                                    true,
                                    $token[2],
                                    $token[3],
                                    $token[4],
                                    $token[5],
                                    $tokenIndex,
                                    $tokenIndex,
                                )
                            );
                            ++$contextStackSize;
                            $namespaceAtoms = array();

                            break;

                        case T_USE:
                            $state = self::STATE_USE_STATEMENT;

                            if (!$contextStack[$contextStackSize - 1][0]) {
                                $contextStack[$contextStackSize - 1] = array(
                                    true,
                                    $token[2],
                                    $token[3],
                                    $token[4],
                                    $token[5],
                                    $tokenIndex,
                                    $tokenIndex,
                                );
                            }

                            $useStatementAtoms = array();
                            $useStatementType = null;
                            $useStatementLine = $token[2];
                            $useStatementColumn = $token[3];
                            $useStatementOffset = $token[4];
                            $useStatementIndex = $tokenIndex;
                            $useStatementAlias = null;
                            $useStatementClauses = array();

                            break;

                        case T_CLASS:
                            $state = self::STATE_SYMBOL;
                            $transitions[] = self::TRANSITION_SYMBOL_START;
                            $symbolType = 'class';

                            break;

                        case T_INTERFACE:
                            $state = self::STATE_SYMBOL;
                            $transitions[] = self::TRANSITION_SYMBOL_START;
                            $symbolType = 'interface';

                            break;

                        case $this->traitTokenType:
                            $state = self::STATE_SYMBOL;
                            $transitions[] = self::TRANSITION_SYMBOL_START;
                            $symbolType = 'trait';

                            break;

                        case T_FUNCTION:
                            $state = self::STATE_SYMBOL;
                            $transitions[] = self::TRANSITION_SYMBOL_START;
                            $symbolType = 'function';

                            break;
                    }

                    break;

                case self::STATE_POTENTIAL_NAMESPACE_NAME:
                    switch ($token[0]) {
                        case T_STRING:
                            $state = self::STATE_NAMESPACE_NAME;

                            if (!$contextStack[$contextStackSize - 2][0]) {
                                $namespaceAtoms[] = $token[1];

                                break;
                            }

                            $transitions[] = self::TRANSITION_CONTEXT_END;
                            $namespaceAtoms[] = $token[1];

                            break;

                        case '{':
                            $state = self::STATE_PHP;

                            $contextStack[$contextStackSize - 1][4] = $token[5];
                            $contextStack[$contextStackSize - 1][6] =
                                $tokenIndex;

                            if (!$contextStack[$contextStackSize - 2][0]) {
                                break;
                            }

                            $transitions[] = self::TRANSITION_CONTEXT_END;

                            break;
                    }

                    break;

                case self::STATE_NAMESPACE_NAME:
                    switch ($token[0]) {
                        case T_STRING:
                            $namespaceAtoms[] = $token[1];

                            break;

                        case ';':
                        case '{':
                            $state = self::STATE_PHP;

                            $namespaceName = new Symbol($namespaceAtoms, true);
                            $contextStack[$contextStackSize - 1][4] = $token[5];
                            $contextStack[$contextStackSize - 1][6] =
                                $tokenIndex;

                            break;
                    }

                    break;

                case self::STATE_USE_STATEMENT:
                    switch ($token[0]) {
                        case T_STRING:
                            $state = self::STATE_USE_STATEMENT_TYPE_NAME;

                            $useStatementAtoms[] = $token[1];

                            break;

                        case T_FUNCTION:
                            $useStatementType = 'function';

                            break;

                        case T_CONST:
                            $useStatementType = 'const';

                            break;
                    }

                    break;

                case self::STATE_USE_STATEMENT_TYPE_NAME:
                    switch ($token[0]) {
                        case T_STRING:
                            $useStatementAtoms[] = $token[1];

                            break;

                        case T_AS:
                            $state = self::STATE_USE_STATEMENT_ALIAS;

                            break;

                        case ',':
                            $transitions[] =
                                self::TRANSITION_USE_STATEMENT_CLAUSE_END;

                            break;

                        case ';':
                            $state = self::STATE_PHP;

                            $transitions[] =
                                self::TRANSITION_USE_STATEMENT_CLAUSE_END;
                            $transitions[] =
                                self::TRANSITION_USE_STATEMENT_END;

                            break;
                    }

                    break;

                case self::STATE_USE_STATEMENT_ALIAS:
                    switch ($token[0]) {
                        case T_STRING:
                            $state = self::STATE_USE_STATEMENT_TYPE_NAME;

                            $useStatementAlias = $token[1];

                            break;
                    }

                    break;

                case self::STATE_SYMBOL:
                    switch ($token[0]) {
                        case T_STRING:
                            $symbolAtoms[] = $token[1];

                            break;

                        case T_EXTENDS:
                        case T_IMPLEMENTS:
                        case '(':
                            $state = self::STATE_SYMBOL_HEADER;

                            break;

                        case '{':
                            $state = self::STATE_SYMBOL_BODY;
                            ++$bracketDepth;

                            break;
                    }

                    break;

                case self::STATE_SYMBOL_HEADER:
                    switch ($token[0]) {
                        case '{':
                            $state = self::STATE_SYMBOL_BODY;
                            ++$bracketDepth;

                            break;
                    }

                    break;

                case self::STATE_SYMBOL_BODY:
                    switch ($token[0]) {
                        case '{':
                            $bracketDepth++;

                            break;

                        case '}':
                            if (0 === --$bracketDepth) {
                                $state = self::STATE_PHP;
                                $transitions[] = self::TRANSITION_SYMBOL_END;
                            }

                            break;
                    }

                    break;
            }

            if ('end' === $token[0]) {
                $transitions[] = self::TRANSITION_CONTEXT_END;
                $isEnd = true;
            }

            foreach ($transitions as $transition) {
                switch ($transition) {
                    case self::TRANSITION_USE_STATEMENT_CLAUSE_END:
                        $useStatementClause = new UseStatementClause(
                            new Symbol($useStatementAtoms, true),
                            $useStatementAlias
                        );
                        $useStatementClauses[] = $useStatementClause;
                        $useStatementAtoms = array();
                        $useStatementAlias = null;

                        break;

                    case self::TRANSITION_USE_STATEMENT_END:
                        $useStatement = new ParsedUseStatement(
                            $useStatementClauses,
                            $useStatementType
                        );
                        $useStatement->line = $useStatementLine;
                        $useStatement->column = $useStatementColumn;
                        $useStatement->offset = $useStatementOffset;
                        $useStatement->size =
                            $token[5] - $useStatementOffset + 1;
                        $useStatement->tokenOffset = $useStatementIndex;
                        $useStatement->tokenSize =
                            $tokenIndex - $useStatementIndex + 1;
                        $useStatements[] = $useStatement;

                        $contextStack[$contextStackSize - 1][4] = $token[5];
                        $contextStack[$contextStackSize - 1][6] =
                            $tokenIndex;

                        break;

                    case self::TRANSITION_CONTEXT_END:
                        $context = new ParsedResolutionContext(
                            $namespaceName,
                            $useStatements
                        );
                        $namespaceName = null;
                        $useStatements = array();

                        if ($isEnd) {
                            $previousContext = \array_pop($contextStack);
                        } else {
                            $thisContext = \array_pop($contextStack);
                            $previousContext = \array_pop($contextStack);
                            $contextStack = array($thisContext);
                            $contextStackSize = 1;
                        }

                        list(
                            $thisContextIsExplicit,
                            $thisContextLine,
                            $thisContextColumn,
                            $thisContextOffset,
                            $thisContextEndOffset,
                            $thisContextIndex,
                            $thisContextEndIndex) = $previousContext;

                        if ($thisContextEndOffset === $thisContextOffset) {
                            $thisContextSize = 0;
                        } else {
                            $thisContextSize =
                                $thisContextEndOffset - $thisContextOffset + 1;
                        }

                        if ($thisContextEndIndex === $thisContextIndex) {
                            $thisContextTokenSize = 0;
                        } else {
                            $thisContextTokenSize =
                                $thisContextEndIndex - $thisContextIndex + 1;
                        }

                        $context->line = $thisContextLine;
                        $context->column = $thisContextColumn;
                        $context->offset = $thisContextOffset;
                        $context->size = $thisContextSize;
                        $context->tokenOffset = $thisContextIndex;
                        $context->tokenSize = $thisContextTokenSize;
                        $context->symbols = $symbols;
                        $contexts[] = $context;

                        $symbols = array();

                        break;

                    case self::TRANSITION_SYMBOL_START:
                        $symbolLine = $token[2];
                        $symbolColumn = $token[3];
                        $symbolOffset = $token[4];
                        $symbolIndex = $tokenIndex;
                        $symbolAtoms = $namespaceAtoms;

                        break;

                    case self::TRANSITION_SYMBOL_END:
                        $symbol = new ParsedSymbol($symbolAtoms, true);
                        $symbol->line = $symbolLine;
                        $symbol->column = $symbolColumn;
                        $symbol->offset = $symbolOffset;
                        $symbol->size = $token[5] - $symbolOffset + 1;
                        $symbol->tokenOffset = $symbolIndex;
                        $symbol->tokenSize = $tokenIndex - $symbolIndex + 1;
                        $symbol->type = $symbolType;
                        $symbols[] = $symbol;

                        break;
                }
            }

            $transitions = array();
        }

        return $contexts;
    }

    private static $instance;
    private $traitTokenType;
}
