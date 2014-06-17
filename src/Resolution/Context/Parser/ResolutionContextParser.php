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

    const TRANSITION_SYMBOL_END = 1;
    const TRANSITION_USE_STATEMENT_END = 2;
    const TRANSITION_CONTEXT_END = 3;

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
        $stateStack = $atoms = $useStatements = $symbols = array();
        $transition = $namespaceName = $useStatementAlias = $useStatementType =
            $symbolType = null;
        $contextPositionStack = array(array(1, 1));
        $symbolBracketDepth = 0;

        foreach ($tokens as $index => $token) {
            switch ($state) {
                case static::STATE_START:
                    switch ($token[0]) {
                        case T_NAMESPACE:
                            array_push($stateStack, $state);
                            $state = static::STATE_POTENTIAL_NAMESPACE_NAME;
                            array_push(
                                $contextPositionStack,
                                array($token[2], $token[3])
                            );

                            break;

                        case T_USE:
                            $state = static::STATE_USE_STATEMENT;

                            break;

                        case T_CLASS:
                            $state = static::STATE_SYMBOL;
                            $symbolType = SymbolType::CLA55();

                            break;

                        case T_INTERFACE:
                            $state = static::STATE_SYMBOL;
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
                            $symbolType = SymbolType::TRA1T();

                            break;

                        case T_FUNCTION:
                            $state = static::STATE_SYMBOL;
                            $symbolType = SymbolType::FUNCT1ON();

                            break;

                        case T_CONST:
                            $state = static::STATE_SYMBOL;
                            $symbolType = SymbolType::CONSTANT();

                            break;
                    }

                    break;

                case static::STATE_POTENTIAL_NAMESPACE_NAME:
                    switch ($token[0]) {
                        case T_NS_SEPARATOR:
                            list($state) = array_pop($stateStack);
                            array_pop($contextPositionStack);

                            break;

                        case T_STRING:
                            $transition = static::TRANSITION_CONTEXT_END;
                            $atoms[] = $token[1];
                            $state = static::STATE_NAMESPACE_NAME;

                            break;

                        case '{':
                            $transition = static::TRANSITION_CONTEXT_END;
                            $state = static::STATE_START;

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
                            $namespaceName = $this->symbolFactory()
                                ->createFromAtoms($atoms, true);
                            $atoms = array();
                            $state = static::STATE_START;

                            break;
                    }

                    break;

                case static::STATE_USE_STATEMENT:
                    switch ($token[0]) {
                        case T_STRING:
                            $atoms[] = $token[1];
                            $state = static::STATE_USE_STATEMENT_CLASS_NAME;

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

                        case ';':
                            $transition = static::TRANSITION_USE_STATEMENT_END;
                            $state = static::STATE_START;

                            break;
                    }

                    break;

                case static::STATE_USE_STATEMENT_ALIAS:
                    switch ($token[0]) {
                        case T_STRING:
                            $useStatementAlias = $this->symbolFactory()
                                ->create($token[1]);
                            $transition = static::TRANSITION_USE_STATEMENT_END;
                            $state = static::STATE_START;

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
                        case '=':
                            $transition = static::TRANSITION_SYMBOL_END;
                            $state = static::STATE_SYMBOL_HEADER;

                            break;

                        case '{':
                            $transition = static::TRANSITION_SYMBOL_END;
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

                        case ';':
                            $state = static::STATE_START;

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
                            }

                            break;
                    }

                    break;
            }

            if ('end' === $token[0]) {
                $transition = static::TRANSITION_CONTEXT_END;
            }

            switch ($transition) {
                case static::TRANSITION_SYMBOL_END:
                    $symbols[] = array(
                        $this->symbolFactory()->createFromAtoms($atoms, false),
                        $symbolType,
                    );
                    $atoms = array();
                    $symbolType = null;

                    break;

                case static::TRANSITION_USE_STATEMENT_END:
                    $useStatements[] = $this->useStatementFactory()->create(
                        $this->symbolFactory()->createFromAtoms($atoms, true),
                        $useStatementAlias,
                        $useStatementType
                    );
                    $atoms = array();
                    $useStatementAlias = $useStatementType = null;

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
                        list($symbol, $type) = $parsedSymbol;

                        $symbols[$index] = new ParsedSymbol(
                            $this->symbolResolver()
                                ->resolveAgainstContext($context, $symbol),
                            $type
                        );
                    }

                    $isContextPositionStacked =
                        count($contextPositionStack) > 2;
                    if ($isContextPositionStacked) {
                        $nextContextPosition = array_pop($contextPositionStack);
                    }

                    list($contextLineNumber, $contextColumnNumber) =
                        array_pop($contextPositionStack);

                    if ($isContextPositionStacked) {
                        array_push($contextPositionStack, $nextContextPosition);
                    }

                    $contexts[] = new ParsedResolutionContext(
                        $context,
                        $symbols,
                        $contextLineNumber,
                        $contextColumnNumber
                    );
                    $symbols = array();

                    break;
            }

            $transition = null;
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
