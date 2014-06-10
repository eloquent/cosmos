<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Parser;

use Eloquent\Cosmos\ClassName\ClassName;
use Eloquent\Cosmos\Resolution\ResolutionContext;
use Eloquent\Cosmos\UseStatement\UseStatement;
use Icecave\Isolator\Isolator;

/**
 * The interface implemented by resolution context parsers.
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
     * @param Isolator|null $isolator The isolator to use.
     */
    public function __construct(Isolator $isolator = null)
    {
        $isolator = Isolator::get($isolator);

        $this->traitTokenType = 'trait';
        if ($isolator->defined('T_TRAIT')) {
            $this->traitTokenType = $isolator->constant('T_TRAIT');
        }
    }

    /**
     * Parse all resolution contexts from the supplied source code.
     *
     * @param string      $source The source code to parse.
     * @param string|null $path   The path, if known.
     *
     * @return array<ParsedResolutionContextInterface> The parsed resolution contexts.
     */
    public function parseSource($source, $path = null)
    {
        $tokens = $this->normalizeTokens(token_get_all($source));

        $contexts = array();
        $this->setState('start');
        $buffer = '';
        $context = null;
        $namespaceName = null;
        $useStatements = array();
        $classNames = array();
        $classBracketDepth = 0;

        foreach ($tokens as $token) {
            switch ($this->state()) {
                case 'start':
                    switch ($token[0]) {
                        case T_NAMESPACE:
                            $this->pushState('namespace-name');

                            break;

                        case T_USE:
                            $this->setState('use-statement-class-name');

                            break;

                        case T_CLASS:
                        case T_INTERFACE:
                        case $this->traitTokenType:
                            $context = new ResolutionContext;
                            $this->setState('class-name');

                            break;
                    }

                    break;

                case 'namespace-name':
                    switch ($token[0]) {
                        case T_NS_SEPARATOR:
                            if ('' === $buffer) {
                                $this->popState();

                                break;
                            }

                        case T_STRING:
                            $buffer .= $token[1];

                            break;

                        case ';':
                        case '{':
                            $namespaceName = ClassName::fromString($buffer)
                                ->toAbsolute();
                            $buffer = '';
                            $this->setState('namespace-header');

                            break;
                    }

                    break;

                case 'namespace-header':
                    switch ($token[0]) {
                        case T_USE:
                            $this->setState('use-statement-class-name');

                            break;

                        case T_NAMESPACE:
                            $this->pushState('namespace-name');

                            break;

                        case T_CLASS:
                        case T_INTERFACE:
                        case $this->traitTokenType:
                            $context = new ResolutionContext(
                                $namespaceName,
                                $useStatements
                            );
                            $useStatements = array();
                            $this->setState('class-name');

                            break;
                    }

                    break;

                case 'use-statement-class-name':
                    switch ($token[0]) {
                        case T_STRING:
                        case T_NS_SEPARATOR:
                            $buffer .= $token[1];

                            break;

                        case T_AS:
                            $this->setState('use-statement-alias');

                            break;

                        case ';':
                            $useStatements[] = new UseStatement(
                                ClassName::fromString($buffer)->toAbsolute()
                            );
                            $buffer = '';
                            $this->setState('namespace-header');

                            break;
                    }

                    break;

                case 'use-statement-alias':
                    switch ($token[0]) {
                        case T_STRING:
                            $useStatements[] = new UseStatement(
                                ClassName::fromString($buffer)->toAbsolute(),
                                ClassName::fromString($token[1])
                            );
                            $buffer = '';
                            $this->setState('namespace-header');

                            break;
                    }

                    break;

                case 'class-name':
                    switch ($token[0]) {
                        case T_STRING:
                        case T_NS_SEPARATOR:
                            $buffer .= $token[1];

                            break;

                        case T_EXTENDS:
                        case T_IMPLEMENTS:
                            $classNames[] = $context->primaryNamespace()
                                ->resolve(ClassName::fromString($buffer));
                            $buffer = '';
                            $this->setState('class-header');

                            break;

                        case '{':
                            $classNames[] = $context->primaryNamespace()
                                ->resolve(ClassName::fromString($buffer));
                            $buffer = '';
                            $this->setState('class-body');
                            $classBracketDepth++;

                            break;
                    }

                    break;

                case 'class-header':
                    switch ($token[0]) {
                        case '{':
                            $this->setState('class-body');
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
                                $this->setState('class-end');
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
                            $this->pushState('namespace-name');

                            break;

                        case T_CLASS:
                        case T_INTERFACE:
                        case $this->traitTokenType:
                            $this->setState('class-name');

                            break;
                    }

                    break;
            }
        }

        if (count($classNames) > 0) {
            $contexts[] = new ParsedResolutionContext($context, $classNames);
        }

        return $contexts;
    }

    private function normalizeTokens($tokens)
    {
        $lineNumber = 0;
        foreach ($tokens as $index => $token) {
            if (is_string($token)) {
                $tokens[$index] = array($token, $token, $lineNumber);
            } else {
                $lineNumber = $token[2];
            }

            $lineNumber += preg_match_all('/$/', $tokens[$index][1], $matches);
        }

        return $tokens;
    }

    private function clearStates()
    {
        $this->stateStack = array();
    }

    private function setState($state)
    {
        // echo 'SET STATE: ' . $state . PHP_EOL;
        $this->stateStack = array($state);
    }

    private function pushState($state)
    {
        // echo 'PUSH STATE: ' . $state . PHP_EOL;
        array_push($this->stateStack, $state);
    }

    private function popState()
    {
        $state = array_pop($this->stateStack);
        // echo 'POP STATE: ' . $state . ' TO ' . $this->state() . PHP_EOL;
        return $state;
    }

    private function state()
    {
        return $this->stateStack[count($this->stateStack) - 1];
    }

    private static $instance;
    private $traitTokenType;
    private $stateStack;
}
