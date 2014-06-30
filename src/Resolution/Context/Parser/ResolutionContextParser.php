<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distrig2ted with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Context\Parser;

use Eloquent\Cosmos\Resolution\Context\Factory\ResolutionContextFactory;
use Eloquent\Cosmos\Resolution\Context\Factory\ResolutionContextFactoryInterface;
use Eloquent\Cosmos\Resolution\Context\Parser\Element\ParsedResolutionContext;
use Eloquent\Cosmos\Resolution\Context\Parser\Element\ParsedResolutionContextInterface;
use Eloquent\Cosmos\Resolution\Context\Parser\Element\ParsedSymbol;
use Eloquent\Cosmos\Resolution\Context\Parser\Element\ParsedUseStatement;
use Eloquent\Cosmos\Resolution\SymbolResolver;
use Eloquent\Cosmos\Resolution\SymbolResolverInterface;
use Eloquent\Cosmos\Symbol\Factory\SymbolFactory;
use Eloquent\Cosmos\Symbol\Factory\SymbolFactoryInterface;
use Eloquent\Cosmos\Symbol\Normalizer\SymbolNormalizer;
use Eloquent\Cosmos\Symbol\Normalizer\SymbolNormalizerInterface;
use Eloquent\Cosmos\Symbol\SymbolType;
use Eloquent\Cosmos\UseStatement\Factory\UseStatementFactory;
use Eloquent\Cosmos\UseStatement\Factory\UseStatementFactoryInterface;
use Eloquent\Cosmos\UseStatement\UseStatement;
use Eloquent\Cosmos\UseStatement\UseStatementType;
use Icecave\Isolator\Isolator;

/**
 * Parses resolution contexts from source code.
 *
 * The behaviour of this class is undefined for syntactically invalid source
 * code.
 */
class ResolutionContextParser implements ResolutionContextParserInterface
{
    const STATE_START = 0;
    const STATE_OPEN_TAG = 1;
    const STATE_PHP = 2;
    const STATE_POTENTIAL_NAMESPACE_NAME = 3;
    const STATE_NAMESPACE_NAME = 4;
    const STATE_USE_STATEMENT = 5;
    const STATE_USE_STATEMENT_CLASS_NAME = 6;
    const STATE_USE_STATEMENT_ALIAS = 7;
    const STATE_SYMBOL = 8;
    const STATE_SYMBOL_HEADER = 9;
    const STATE_SYMBOL_BODY = 10;

    const TRANSITION_SYMBOL_START = 1;
    const TRANSITION_USE_STATEMENT_CLAUSE_END = 2;
    const TRANSITION_USE_STATEMENT_END = 3;
    const TRANSITION_CONTEXT_END = 4;

    /**
     * Get a static instance of this parser.
     *
     * @return ResolutionContextParserInterface The static parser.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Construct a new resolution context parser.
     *
     * @param SymbolFactoryInterface|null            $symbolFactory       The symbol factory to use.
     * @param SymbolResolverInterface|null           $symbolResolver      The symbol resolver to use.
     * @param SymbolNormalizerInterface|null         $symbolNormalizer    The symbol normalizer to use.
     * @param UseStatementFactoryInterface|null      $useStatementFactory The use statement factory to use.
     * @param ResolutionContextFactoryInterface|null $contextFactory      The resolution context factory to use.
     * @param TokenNormalizerInterface|null          $tokenNormalizer     The token normalizer to use.
     * @param Isolator|null                          $isolator            The isolator to use.
     */
    public function __construct(
        SymbolFactoryInterface $symbolFactory = null,
        SymbolResolverInterface $symbolResolver = null,
        SymbolNormalizerInterface $symbolNormalizer = null,
        UseStatementFactoryInterface $useStatementFactory = null,
        ResolutionContextFactoryInterface $contextFactory = null,
        TokenNormalizerInterface $tokenNormalizer = null,
        Isolator $isolator = null
    ) {
        if (null === $symbolFactory) {
            $symbolFactory = SymbolFactory::instance();
        }
        if (null === $symbolResolver) {
            $symbolResolver = SymbolResolver::instance();
        }
        if (null === $symbolNormalizer) {
            $symbolNormalizer = SymbolNormalizer::instance();
        }
        if (null === $useStatementFactory) {
            $useStatementFactory = UseStatementFactory::instance();
        }
        if (null === $contextFactory) {
            $contextFactory = ResolutionContextFactory::instance();
        }
        if (null === $tokenNormalizer) {
            $tokenNormalizer = TokenNormalizer::instance();
        }

        $this->symbolFactory = $symbolFactory;
        $this->symbolResolver = $symbolResolver;
        $this->symbolNormalizer = $symbolNormalizer;
        $this->useStatementFactory = $useStatementFactory;
        $this->contextFactory = $contextFactory;
        $this->tokenNormalizer = $tokenNormalizer;
        $isolator = Isolator::get($isolator);

        $this->traitTokenType = 'trait';
        if ($isolator->defined('T_TRAIT')) {
            $this->traitTokenType = $isolator->constant('T_TRAIT');
        }
    }

    /**
     * Get the symbol factory.
     *
     * @return SymbolFactoryInterface The symbol factory.
     */
    public function symbolFactory()
    {
        return $this->symbolFactory;
    }

    /**
     * Get the symbol resolver.
     *
     * @return SymbolResolverInterface The symbol resolver.
     */
    public function symbolResolver()
    {
        return $this->symbolResolver;
    }

    /**
     * Get the symbol normalizer.
     *
     * @return SymbolNormalizerInterface The symbol normalizer.
     */
    public function symbolNormalizer()
    {
        return $this->symbolNormalizer;
    }

    /**
     * Get the use statement factory.
     *
     * @return UseStatementFactoryInterface The use statement factory.
     */
    public function useStatementFactory()
    {
        return $this->useStatementFactory;
    }

    /**
     * Get the resolution context factory.
     *
     * @return ResolutionContextFactoryInterface The resolution context factory.
     */
    public function contextFactory()
    {
        return $this->contextFactory;
    }

    /**
     * Get the token normalizer.
     *
     * @return TokenNormalizerInterface The token normalizer.
     */
    public function tokenNormalizer()
    {
        return $this->tokenNormalizer;
    }

    /**
     * Parse all resolution contexts from the supplied source code.
     *
     * @param string $source The source code to parse.
     *
     * @return array<ParsedResolutionContextInterface> The parsed resolution contexts.
     */
    public function parseSource($source)
    {
        $tokens = $this->tokenNormalizer()
            ->normalizeTokens(token_get_all($source));
        $numTokens = count($tokens);
        $contexts = array();

        $state = static::STATE_START;
        $previousState = null;
        $transitions = array();
        $isEnd = false;
        $contextStack = array(
            array(false, new ParserPosition(0, 0), 0, 0, 0, 0)
        );
        $contextStackSize = 1;
        $atoms = null;
        $namespaceName = null;
        $useStatementType = null;
        $useStatementPosition = null;
        $useStatementOffset = null;
        $useStatementIndex = null;
        $useStatementAlias = null;
        $useStatementClauses = array();
        $useStatements = array();
        $numUseStatements = 0;
        $symbolType = null;
        $symbolPosition = null;
        $symbolOffset = null;
        $symbolIndex = null;
        $symbolBracketDepth = 0;
        $symbols = array();
        $numSymbols = 0;

        foreach ($tokens as $tokenIndex => $token) {
            switch ($state) {
                case static::STATE_START:
                    switch ($token[0]) {
                        case T_OPEN_TAG:
                            $state = static::STATE_OPEN_TAG;
                    }

                    break;

                case static::STATE_OPEN_TAG:
                    $state = static::STATE_PHP;

                    $contextStack = array(
                        array(
                            false,
                            new ParserPosition($token[2], $token[3]),
                            $token[4],
                            $token[4],
                            $tokenIndex,
                            $tokenIndex,
                        )
                    );
                    $contextStackSize = 1;

                    break;

                case static::STATE_PHP:
                    switch ($token[0]) {
                        case T_NAMESPACE:
                            $previousState = $state;
                            $state = static::STATE_POTENTIAL_NAMESPACE_NAME;

                            array_push(
                                $contextStack,
                                array(
                                    true,
                                    new ParserPosition($token[2], $token[3]),
                                    $token[4],
                                    $token[5],
                                    $tokenIndex,
                                    $tokenIndex,
                                )
                            );
                            $contextStackSize++;
                            $atoms = array();

                            break;

                        case T_USE:
                            $state = static::STATE_USE_STATEMENT;

                            if (!$contextStack[$contextStackSize - 1][0]) {
                                $contextStack[$contextStackSize - 1] = array(
                                    true,
                                    new ParserPosition($token[2], $token[3]),
                                    $token[4],
                                    $token[5],
                                    $tokenIndex,
                                    $tokenIndex,
                                );
                            }

                            $useStatementType = UseStatementType::TYPE();
                            $useStatementPosition =
                                new ParserPosition($token[2], $token[3]);
                            $useStatementOffset = $token[4];
                            $useStatementIndex = $tokenIndex;
                            $atoms = array();
                            $useStatementAlias = null;
                            $useStatementClauses = array();

                            break;

                        case T_CLASS:
                            $state = static::STATE_SYMBOL;

                            $symbolType = SymbolType::CLA55();
                            $transitions[] = static::TRANSITION_SYMBOL_START;

                            break;

                        case T_INTERFACE:
                            $state = static::STATE_SYMBOL;

                            $symbolType = SymbolType::INTERF4CE();
                            $transitions[] = static::TRANSITION_SYMBOL_START;

                            break;

                        // @codeCoverageIgnoreStart
                        case T_STRING:
                            if ('trait' !== strtolower($token[1])) {
                                break;
                            }
                        // @codeCoverageIgnoreEnd

                        case $this->traitTokenType:
                            $state = static::STATE_SYMBOL;

                            $symbolType = SymbolType::TRA1T();
                            $transitions[] = static::TRANSITION_SYMBOL_START;

                            break;

                        case T_FUNCTION:
                            $state = static::STATE_SYMBOL;

                            $symbolType = SymbolType::FUNCT1ON();
                            $transitions[] = static::TRANSITION_SYMBOL_START;

                            break;
                    }

                    break;

                case static::STATE_POTENTIAL_NAMESPACE_NAME:
                    switch ($token[0]) {
                        case T_NS_SEPARATOR:
                            $state = $previousState;

                            array_pop($contextStack);
                            $contextStackSize--;

                            break;

                        case T_STRING:
                            $state = static::STATE_NAMESPACE_NAME;

                            if (
                                $numSymbols < 1 &&
                                !$contextStack[$contextStackSize - 2][0]
                            ) {
                                $atoms[] = $token[1];

                                break;
                            }

                            $transitions[] = static::TRANSITION_CONTEXT_END;

                            break;

                        case '{':
                            $state = static::STATE_PHP;

                            $contextStack[$contextStackSize - 1][3] = $token[5];
                            $contextStack[$contextStackSize - 1][5] =
                                $tokenIndex;

                            if (
                                $numSymbols < 1 &&
                                !$contextStack[$contextStackSize - 2][0]
                            ) {
                                break;
                            }

                            $transitions[] = static::TRANSITION_CONTEXT_END;

                            break;
                    }

                    break;

                case static::STATE_NAMESPACE_NAME:
                    switch ($token[0]) {
                        case T_STRING:
                            $atoms[] = $token[1];

                            break;

                        case ';':
                        case '{':
                            $state = static::STATE_PHP;

                            $namespaceName = $this->symbolFactory()
                                ->createFromAtoms($atoms, true);
                            $contextStack[$contextStackSize - 1][3] = $token[5];
                            $contextStack[$contextStackSize - 1][5] =
                                $tokenIndex;

                            break;
                    }

                    break;

                case static::STATE_USE_STATEMENT:
                    switch ($token[0]) {
                        case T_STRING:
                            $state = static::STATE_USE_STATEMENT_CLASS_NAME;

                            $atoms[] = $token[1];

                            break;

                        case T_FUNCTION:
                            $useStatementType = UseStatementType::FUNCT1ON();

                            break;

                        case T_CONST:
                            $useStatementType = UseStatementType::CONSTANT();

                            break;
                    }

                    break;

                case static::STATE_USE_STATEMENT_CLASS_NAME:
                    switch ($token[0]) {
                        case T_STRING:
                            $atoms[] = $token[1];

                            break;

                        case T_AS:
                            $state = static::STATE_USE_STATEMENT_ALIAS;

                            break;

                        case ',':
                            $transitions[] =
                                static::TRANSITION_USE_STATEMENT_CLAUSE_END;

                            break;

                        case ';':
                            $state = static::STATE_PHP;

                            $transitions[] =
                                static::TRANSITION_USE_STATEMENT_CLAUSE_END;
                            $transitions[] =
                                static::TRANSITION_USE_STATEMENT_END;

                            break;
                    }

                    break;

                case static::STATE_USE_STATEMENT_ALIAS:
                    switch ($token[0]) {
                        case T_STRING:
                            $state = static::STATE_USE_STATEMENT_CLASS_NAME;

                            $useStatementAlias = $this->symbolFactory()
                                ->create($token[1]);

                            break;
                    }

                    break;

                case static::STATE_SYMBOL:
                    switch ($token[0]) {
                        case T_STRING:
                            $atoms[] = $token[1];

                            break;

                        case T_EXTENDS:
                        case T_IMPLEMENTS:
                        case '(':
                            $state = static::STATE_SYMBOL_HEADER;

                            break;

                        case '{':
                            $state = static::STATE_SYMBOL_BODY;

                            $symbolBracketDepth++;

                            break;
                    }

                    break;

                case static::STATE_SYMBOL_HEADER:
                    switch ($token[0]) {
                        case '{':
                            $state = static::STATE_SYMBOL_BODY;

                            $symbolBracketDepth++;

                            break;
                    }

                    break;

                case static::STATE_SYMBOL_BODY:
                    switch ($token[0]) {
                        case '{':
                            $symbolBracketDepth++;

                            break;

                        case '}':
                            if (0 === --$symbolBracketDepth) {
                                $state = static::STATE_PHP;

                                $symbols[] = array(
                                    $symbolType,
                                    $symbolPosition,
                                    $symbolOffset,
                                    $token[5],
                                    $this->symbolFactory()
                                        ->createFromAtoms($atoms, false),
                                );
                                $numSymbols++;
                            }

                            break;
                    }

                    break;
            }

            if ('end' === $token[0]) {
                $transitions[] = static::TRANSITION_CONTEXT_END;
                $isEnd = true;
            }

            foreach ($transitions as $transition) {
                switch ($transition) {
                    case static::TRANSITION_SYMBOL_START:
                        $symbolPosition =
                            new ParserPosition($token[2], $token[3]);
                        $symbolOffset = $token[4];
                        $symbolIndex = $tokenIndex;
                        $atoms = array();

                        break;

                    case static::TRANSITION_USE_STATEMENT_CLAUSE_END:
                        $useStatementClauses[] = $this
                            ->useStatementFactory()
                            ->createClause(
                                $this->symbolFactory()
                                    ->createFromAtoms($atoms, true),
                                $useStatementAlias
                            );
                        $atoms = array();
                        $useStatementAlias = null;

                        break;

                    case static::TRANSITION_USE_STATEMENT_END:
                        $useStatements[] = new ParsedUseStatement(
                            $this->useStatementFactory()->createStatement(
                                $useStatementClauses,
                                $useStatementType
                            ),
                            $useStatementPosition,
                            $useStatementOffset,
                            $token[5] - $useStatementOffset + 1
                        );
                        $numUseStatements++;
                        $contextStack[$contextStackSize - 1][3] = $token[5];
                        $contextStack[$contextStackSize - 1][5] =
                            $tokenIndex;

                        break;

                    case static::TRANSITION_CONTEXT_END:
                        $context = $this->contextFactory()
                            ->create($namespaceName, $useStatements);
                        $namespaceName = null;
                        $useStatements = array();
                        $numUseStatements = 0;

                        foreach ($symbols as $symbolIndex => $thisSymbol) {
                            list(
                                $thisSymbolType,
                                $thisSymbolPosition,
                                $thisSymbolOffset,
                                $thisSymbolEndOffset,
                                $symbol,
                            ) = $thisSymbol;

                            $symbols[$symbolIndex] = new ParsedSymbol(
                                $this->symbolResolver()
                                    ->resolveAgainstContext(
                                        $context,
                                        $symbol
                                    ),
                                $thisSymbolType,
                                $thisSymbolPosition,
                                $thisSymbolOffset,
                                $thisSymbolEndOffset - $thisSymbolOffset + 1
                            );
                        }

                        if (!$isEnd) {
                            $thisContext = array_pop($contextStack);
                        }

                        $previousContext = array_pop($contextStack);

                        if (!$isEnd) {
                            $contextStack = array($thisContext);
                            $contextStackSize = 1;
                        }

                        list(
                            $thisContextIsExplicit,
                            $thisContextPosition,
                            $thisContextOffset,
                            $thisContextEndOffset,
                            $thisContextIndex,
                            $thisContextEndIndex,
                        ) = $previousContext;

                        if ($thisContextEndOffset === $thisContextOffset) {
                            $thisContextSize = 0;
                        } else {
                            $thisContextSize = $thisContextEndOffset -
                                $thisContextOffset + 1;
                        }

                        if ($thisContextIsExplicit) {
                            $thisContextTokens = array_slice(
                                $tokens,
                                $thisContextIndex,
                                $thisContextEndIndex - $thisContextIndex + 1
                            );
                        } else {
                            $thisContextTokens = array();
                        }

                        $contexts[] = new ParsedResolutionContext(
                            $context,
                            $symbols,
                            $thisContextPosition,
                            $thisContextOffset,
                            $thisContextSize,
                            $thisContextTokens
                        );

                        $symbols = array();
                        $numSymbols = 0;
                        $atoms[] = $token[1];

                        break;
                }
            }

            $transitions = array();
        }

        return $contexts;
    }

    private static $instance;
    private $symbolFactory;
    private $symbolResolver;
    private $symbolNormalizer;
    private $useStatementFactory;
    private $contextFactory;
    private $tokenNormalizer;
    private $traitTokenType;
}
