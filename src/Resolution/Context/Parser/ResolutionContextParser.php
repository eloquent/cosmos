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
    const STATE_POTENTIAL_NAMESPACE_NAME = 1;
    const STATE_NAMESPACE_NAME = 2;
    const STATE_USE_STATEMENT = 3;
    const STATE_USE_STATEMENT_CLASS_NAME = 4;
    const STATE_USE_STATEMENT_ALIAS = 5;
    const STATE_SYMBOL = 6;
    const STATE_SYMBOL_HEADER = 7;
    const STATE_SYMBOL_BODY = 8;

    const TRANSITION_SYMBOL_START = 1;
    const TRANSITION_SYMBOL_END = 2;
    const TRANSITION_USE_STATEMENT_CLAUSE_END = 3;
    const TRANSITION_USE_STATEMENT_END = 4;
    const TRANSITION_CONTEXT_END = 5;

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
        $contexts = array();

        $state = static::STATE_START;
        $stateStack = $transitions = $atoms = $useStatementClauses =
            $useStatements = $symbols = array();
        $namespaceName = $useStatementAlias = $useStatementType =
            $useStatementPosition = $symbolType = $symbolPosition = null;
        $contextMetaStack = array(
            array(new ParserPosition(1, 1), 0, false, null, null, 0),
        );
        $contextMetaStackSize = 1;
        $startOffset = $endOffset = $contextEndOffset =
            $useStatementStartOffset = $symbolStartOffset =
            $symbolBracketDepth = 0;

        foreach ($tokens as $index => $token) {
            $startOffset = $endOffset + 1;
            $endOffset = $startOffset + strlen($token[1]) - 1;

            switch ($state) {
                case static::STATE_START:
                    switch ($token[0]) {
                        case T_NAMESPACE:
                            $state = static::STATE_POTENTIAL_NAMESPACE_NAME;
                            array_push($stateStack, $state);
                            array_push(
                                $contextMetaStack,
                                array(
                                    new ParserPosition($token[2], $token[3]),
                                    $startOffset,
                                    true,
                                    null,
                                    null,
                                    0
                                )
                            );
                            $contextMetaStackSize++;

                            break;

                        case T_USE:
                            $state = static::STATE_USE_STATEMENT;
                            $useStatementPosition =
                                new ParserPosition($token[2], $token[3]);
                            $useStatementStartOffset = $startOffset;

                            break;

                        case T_CLASS:
                            $state = static::STATE_SYMBOL;
                            $transitions[] = static::TRANSITION_SYMBOL_START;
                            $symbolType = SymbolType::CLA55();

                            break;

                        case T_INTERFACE:
                            $state = static::STATE_SYMBOL;
                            $transitions[] = static::TRANSITION_SYMBOL_START;
                            $symbolType = SymbolType::INTERF4CE();

                            break;

                        // @codeCoverageIgnoreStart
                        case T_STRING:
                            if ('trait' !== strtolower($token[1])) {
                                break;
                            }
                        // @codeCoverageIgnoreEnd

                        case $this->traitTokenType:
                            $state = static::STATE_SYMBOL;
                            $transitions[] = static::TRANSITION_SYMBOL_START;
                            $symbolType = SymbolType::TRA1T();

                            break;

                        case T_FUNCTION:
                            $state = static::STATE_SYMBOL;
                            $transitions[] = static::TRANSITION_SYMBOL_START;
                            $symbolType = SymbolType::FUNCT1ON();

                            break;
                    }

                    break;

                case static::STATE_POTENTIAL_NAMESPACE_NAME:
                    switch ($token[0]) {
                        case T_NS_SEPARATOR:
                            list($state) = array_pop($stateStack);
                            array_pop($contextMetaStack);
                            $contextMetaStackSize--;

                            break;

                        case T_STRING:
                            $state = static::STATE_NAMESPACE_NAME;
                            $transitions[] = static::TRANSITION_CONTEXT_END;
                            $atoms[] = $token[1];
                            $contextMetaStack[$contextMetaStackSize - 1][3] =
                                $startOffset;
                            $contextMetaStack[$contextMetaStackSize - 1][4] =
                                $endOffset;

                            break;

                        case '{':
                            $state = static::STATE_START;
                            $transitions[] = static::TRANSITION_CONTEXT_END;
                            $contextMetaStack[$contextMetaStackSize - 1][5] =
                                $endOffset + 1;

                            break;
                    }

                    break;

                case static::STATE_NAMESPACE_NAME:
                    switch ($token[0]) {
                        case T_STRING:
                            $atoms[] = $token[1];
                            $contextMetaStack[$contextMetaStackSize - 1][4] =
                                $endOffset;

                            break;

                        case ';':
                        case '{':
                            $state = static::STATE_START;
                            $namespaceName = $this->symbolFactory()
                                ->createFromAtoms($atoms, true);
                            $atoms = array();
                            $contextEndOffset = $endOffset;
                            $contextMetaStack[$contextMetaStackSize - 1][5] =
                                $endOffset + 1;

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
                            $state = static::STATE_START;
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
                                $state = static::STATE_START;
                                $transitions[] = static::TRANSITION_SYMBOL_END;
                            }

                            break;
                    }

                    break;
            }

            if ('end' === $token[0]) {
                $transitions[] = static::TRANSITION_CONTEXT_END;
            }

            foreach ($transitions as $transition) {
                switch ($transition) {
                    case static::TRANSITION_SYMBOL_START:
                        $symbolPosition =
                            new ParserPosition($token[2], $token[3]);
                        $symbolStartOffset = $startOffset;

                        break;

                    case static::TRANSITION_SYMBOL_END:
                        $symbols[] = array(
                            $this->symbolFactory()
                                ->createFromAtoms($atoms, false),
                            $symbolType,
                            $symbolPosition,
                            $symbolStartOffset,
                            $endOffset,
                        );
                        $atoms = array();
                        $symbolType = null;

                        break;

                    case static::TRANSITION_USE_STATEMENT_CLAUSE_END:
                        $useStatementClauses[] = $this->useStatementFactory()
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
                            $useStatementStartOffset,
                            $endOffset - $useStatementStartOffset + 1
                        );
                        $useStatementClauses = $atoms = array();
                        $useStatementType = null;
                        $contextEndOffset = $endOffset;

                        break;

                    case static::TRANSITION_CONTEXT_END:
                        if (
                            'end' !== $token[0] &&
                            null === $namespaceName &&
                            0 === count($useStatements) &&
                            0 === count($symbols)
                        ) {
                            break;
                        }

                        $context = $this->contextFactory()
                            ->create($namespaceName, $useStatements);
                        $namespaceName = null;
                        $useStatements = array();

                        foreach ($symbols as $index => $parsedSymbol) {
                            list(
                                $symbol,
                                $type,
                                $position,
                                $symbolStartOffset,
                                $symbolEndOffset,
                            ) = $parsedSymbol;

                            $symbols[$index] = new ParsedSymbol(
                                $this->symbolResolver()
                                    ->resolveAgainstContext($context, $symbol),
                                $type,
                                $position,
                                $symbolStartOffset,
                                $symbolEndOffset - $symbolStartOffset + 1
                            );
                        }

                        $isContextMetaStacked = $contextMetaStackSize > 2;
                        if ($isContextMetaStacked) {
                            $nextContextMeta = array_pop($contextMetaStack);
                        }

                        list(
                            $contextPosition,
                            $contextStartOffset,
                            $isExplicitNamespace,
                            $namespaceSymbolStartOffset,
                            $namespaceSymbolEndOffset,
                            $namespaceBodyOffset,
                        ) = array_pop($contextMetaStack);
                        $contextMetaStackSize--;

                        if ($isContextMetaStacked) {
                            array_push($contextMetaStack, $nextContextMeta);
                        }

                        if (!$isExplicitNamespace) {
                            $useStatements = $context->useStatements();

                            if (count($useStatements) > 0) {
                                $contextStartOffset = $useStatements[0]
                                    ->offset();
                            }

                            $useStatements = array();
                        }

                        if (0 === $contextEndOffset) {
                            $contextSize = 0;
                        } else {
                            $contextSize = $contextEndOffset -
                                $contextStartOffset + 1;
                        }

                        $contexts[] = new ParsedResolutionContext(
                            $context,
                            $symbols,
                            $contextPosition,
                            $contextStartOffset,
                            $contextSize,
                            $namespaceSymbolStartOffset,
                            $namespaceSymbolEndOffset -
                                $namespaceSymbolStartOffset + 1,
                            $namespaceBodyOffset
                        );
                        $symbols = array();

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
