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
        $context = new ResolutionContext;
        do {
            $classNames = $this->parseClassNames($tokens);
            foreach ($classNames as $index => $className) {
                $classNames[$index] = $className
                    ->resolveAgainst($context->primaryNamespace());
            }

            $contexts[] = new ParsedResolutionContext($context, $classNames);
        } while ($context = $this->parseContext($tokens));

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

            $lineNumber += preg_match_all('/$/', $tokens[$index][1]);
        }

        return $tokens;
    }

    private function parseContext(&$tokens)
    {
        return null;
    }

    private function parseClassNames(&$tokens)
    {
        $classNames = array();
        while ($className = $this->parseClassName($tokens)) {
            $classNames[] = $className;
        }

        return $classNames;
    }

    private function parseClassName(&$tokens)
    {
        $typeTypes = array(T_CLASS, T_INTERFACE);
        if (defined('T_TRAIT')) {
            $typeTypes[] = T_TRAIT;
        }

        if (!$token = $this->consumeUntil($typeTypes, $tokens)) {
            return null;
        }
        if (!$token = $this->consumeUntil(T_STRING, $tokens)) {
            return null;
        }

        return ClassName::fromString($token[1]);
    }

    private function consumeUntil($types, &$tokens)
    {
        if (!is_array($types)) {
            $types = array($types);
        }

        $token = current($tokens);
        while ($token && !in_array($token[0], $types)) {
            $token = next($tokens);
        }

        return $token;
    }

    private static $instance;
}
