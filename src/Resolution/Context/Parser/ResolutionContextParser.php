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

use Eloquent\Cosmos\ClassName\ClassName;
use Eloquent\Cosmos\ClassName\Factory\ClassNameFactory;
use Eloquent\Cosmos\ClassName\Factory\ClassNameFactoryInterface;
use Eloquent\Cosmos\ClassName\Normalizer\ClassNameNormalizer;
use Eloquent\Cosmos\ClassName\Normalizer\ClassNameNormalizerInterface;
use Eloquent\Cosmos\Resolution\ClassNameResolver;
use Eloquent\Cosmos\Resolution\ClassNameResolverInterface;
use Eloquent\Cosmos\Resolution\Context\Factory\ResolutionContextFactory;
use Eloquent\Cosmos\Resolution\Context\Factory\ResolutionContextFactoryInterface;
use Eloquent\Cosmos\UseStatement\Factory\UseStatementFactory;
use Eloquent\Cosmos\UseStatement\Factory\UseStatementFactoryInterface;
use Eloquent\Cosmos\UseStatement\UseStatement;
use Icecave\Isolator\Isolator;

/**
 * Parses resolution contexts from source code.
 *
 * The behaviour of this class is undefined for syntactically invalid source
 * code.
 */
class ResolutionContextParser implements ResolutionContextParserInterface
{
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
     * @param ClassNameFactoryInterface|null         $classNameFactory    The class name factory to use.
     * @param ClassNameResolverInterface|null        $classNameResolver   The class name resolver to use.
     * @param ClassNameNormalizerInterface|null      $classNameNormalizer The class name normalizer to use.
     * @param UseStatementFactoryInterface|null      $useStatementFactory The use statement factory to use.
     * @param ResolutionContextFactoryInterface|null $contextFactory      The resolution context factory to use.
     * @param Isolator|null                          $isolator            The isolator to use.
     */
    public function __construct(
        ClassNameFactoryInterface $classNameFactory = null,
        ClassNameResolverInterface $classNameResolver = null,
        ClassNameNormalizerInterface $classNameNormalizer = null,
        UseStatementFactoryInterface $useStatementFactory = null,
        ResolutionContextFactoryInterface $contextFactory = null,
        Isolator $isolator = null
    ) {
        if (null === $classNameFactory) {
            $classNameFactory = ClassNameFactory::instance();
        }
        if (null === $classNameResolver) {
            $classNameResolver = ClassNameResolver::instance();
        }
        if (null === $classNameNormalizer) {
            $classNameNormalizer = ClassNameNormalizer::instance();
        }
        if (null === $useStatementFactory) {
            $useStatementFactory = UseStatementFactory::instance();
        }
        if (null === $contextFactory) {
            $contextFactory = ResolutionContextFactory::instance();
        }

        $this->classNameFactory = $classNameFactory;
        $this->classNameResolver = $classNameResolver;
        $this->classNameNormalizer = $classNameNormalizer;
        $this->useStatementFactory = $useStatementFactory;
        $this->contextFactory = $contextFactory;
        $isolator = Isolator::get($isolator);

        $this->traitTokenType = 'trait';
        if ($isolator->defined('T_TRAIT')) {
            $this->traitTokenType = $isolator->constant('T_TRAIT');
        }
    }

    /**
     * Get the class name factory.
     *
     * @return ClassNameFactoryInterface The class name factory.
     */
    public function classNameFactory()
    {
        return $this->classNameFactory;
    }

    /**
     * Get the class name resolver.
     *
     * @return ClassNameResolverInterface The class name resolver.
     */
    public function classNameResolver()
    {
        return $this->classNameResolver;
    }

    /**
     * Get the class name normalizer.
     *
     * @return ClassNameNormalizerInterface The class name normalizer.
     */
    public function classNameNormalizer()
    {
        return $this->classNameNormalizer;
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
     * Parse all resolution contexts from the supplied source code.
     *
     * @param string $source The source code to parse.
     *
     * @return array<ParsedResolutionContextInterface> The parsed resolution contexts.
     */
    public function parseSource($source)
    {
        $tokens = $this->normalizeTokens(token_get_all($source));
        $contexts = array();

        $state = 'start';
        $stateStack = array();
        $transition = null;
        $atoms = array();
        $context = null;
        $namespaceName = null;
        $useStatements = array();
        $useStatementAlias = null;
        $classNames = array();
        $classBracketDepth = 0;

        foreach ($tokens as $token) {
            switch ($state) {
                case 'start':
                    switch ($token[0]) {
                        case T_NAMESPACE:
                            array_push($stateStack, $state);
                            $state = 'namespace-name';

                            break;

                        case T_USE:
                            $state = 'use-statement-class-name';

                            break;

                        case T_CLASS:
                        case T_INTERFACE:
                        case $this->traitTokenType:
                            $context = $this->contextFactory()->create();
                            $state = 'class-name';

                            break;
                    }

                    break;

                case 'namespace-name':
                    switch ($token[0]) {
                        case T_NS_SEPARATOR:
                            if (array() === $atoms) {
                                $state = array_pop($stateStack);
                            }

                            break;

                        case T_STRING:
                            $atoms[] = $token[1];

                            break;

                        case ';':
                        case '{':
                            $namespaceName = $this->classNameFactory()
                                ->createFromAtoms($atoms, true);
                            $atoms = array();
                            $state = 'namespace-header';

                            break;
                    }

                    break;

                case 'namespace-header':
                    switch ($token[0]) {
                        case T_USE:
                            $state = 'use-statement-class-name';

                            break;

                        case T_NAMESPACE:
                            array_push($stateStack, $state);
                            $state = 'namespace-name';

                            break;

                        case T_CLASS:
                        case T_INTERFACE:
                        case $this->traitTokenType:
                            $context = $this->contextFactory()
                                ->create($namespaceName, $useStatements);
                            $useStatements = array();
                            $state = 'class-name';

                            break;
                    }

                    break;

                case 'use-statement-class-name':
                    switch ($token[0]) {
                        case T_STRING:
                            $atoms[] = $token[1];

                            break;

                        case T_AS:
                            $state = 'use-statement-alias';

                            break;

                        case ';':
                            $transition = 'use-statement-end';
                            $state = 'namespace-header';

                            break;
                    }

                    break;

                case 'use-statement-alias':
                    switch ($token[0]) {
                        case T_STRING:
                            $useStatementAlias = $this->classNameFactory()
                                ->create($token[1]);
                            $transition = 'use-statement-end';
                            $state = 'namespace-header';

                            break;
                    }

                    break;

                case 'class-name':
                    switch ($token[0]) {
                        case T_STRING:
                            $atoms[] = $token[1];

                            break;

                        case T_EXTENDS:
                        case T_IMPLEMENTS:
                            $transition = 'class-name-end';
                            $state = 'class-header';

                            break;

                        case '{':
                            $transition = 'class-name-end';
                            $state = 'class-body';
                            $classBracketDepth++;

                            break;
                    }

                    break;

                case 'class-header':
                    switch ($token[0]) {
                        case '{':
                            $state = 'class-body';
                            $classBracketDepth++;

                            break;
                    }

                    break;

                case 'class-body':
                    switch ($token[0]) {
                        case '{':
                            $classBracketDepth++;

                            break;

                        case '}':
                            if (0 === --$classBracketDepth) {
                                $state = 'class-end';
                            }

                            break;
                    }

                    break;

                case 'class-end':
                    switch ($token[0]) {
                        case T_NAMESPACE:
                            $contexts[] = new ParsedResolutionContext(
                                $context,
                                $classNames
                            );
                            $classNames = array();

                            array_push($stateStack, $state);
                            $state = 'namespace-name';

                            break;

                        case T_CLASS:
                        case T_INTERFACE:
                        case $this->traitTokenType:
                            $state = 'class-name';

                            break;
                    }

                    break;
            }

            switch ($transition) {
                case 'class-name-end':
                    $classNames[] = $this->classNameNormalizer()
                        ->normalize(
                            $this->classNameResolver()->resolve(
                                $context->primaryNamespace(),
                                $this->classNameFactory()
                                    ->createFromAtoms($atoms, false)
                            )
                        );
                    $atoms = array();

                    break;

                case 'use-statement-end':
                    $useStatements[] = $this->useStatementFactory()
                        ->create(
                            $this->classNameFactory()
                                ->createFromAtoms($atoms, true),
                            $useStatementAlias
                        );
                    $atoms = array();
                    $useStatementAlias = null;

                    break;
            }

            $transition = null;
        }

        $contexts[] = new ParsedResolutionContext($context, $classNames);

        return $contexts;
    }

    private function normalizeTokens($tokens)
    {
        foreach ($tokens as $index => $token) {
            if (is_string($token)) {
                $tokens[$index] = array($token, $token, 0);
            }
        }

        return $tokens;
    }

    private static $instance;
    private $classNameFactory;
    private $classNameResolver;
    private $classNameNormalizer;
    private $useStatementFactory;
    private $contextFactory;
    private $traitTokenType;
}
