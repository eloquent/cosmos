<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Parser;

use Eloquent\Liberator\Liberator;
use PHPUnit_Framework_TestCase;

/**
 * @covers \Eloquent\Cosmos\Parser\ResolutionContextParser
 */
class ResolutionContextParserTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->subject = new ResolutionContextParser();

        $this->tokenNormalizer = new TokenNormalizer();
        $this->fixturePath = __DIR__ . '/../../fixture/context-parser';
    }

    public function parseTokensData()
    {
        $data = array();

        foreach (scandir(__DIR__ . '/../../fixture/context-parser') as $name) {
            if ('.' !== $name[0]) {
                $data[$name] = array($name);
            }
        }

        return $data;
    }

    /**
     * @dataProvider parseTokensData
     */
    public function testParseTokens($name)
    {
        if (is_file($this->fixturePath . '/' . $name . '/supported.php')) {
            $isSupported = require $this->fixturePath . '/' . $name . '/supported.php';

            if (!$isSupported) {
                $this->markTestSkipped($message);
            }
        }

        $tokens = $this->tokenNormalizer
            ->normalizeTokens(token_get_all(file_get_contents($this->fixturePath . '/' . $name . '/source.php')));
        $actual = $this->subject->parseTokens($tokens);

        $expectedParsed = trim(file_get_contents($this->fixturePath . '/' . $name . '/parsed.php'));
        $expectedDetails = require $this->fixturePath . '/' . $name . '/details.php';

        $this->assertSame($expectedParsed, $this->renderParsedContexts($actual));
        $this->assertEquals($expectedDetails, $this->contextDetails($actual, $tokens));
    }

    public function testInstance()
    {
        $class = get_class($this->subject);
        $liberatedClass = Liberator::liberateClass($class);
        $liberatedClass->instance = null;
        $actual = $class::instance();

        $this->assertInstanceOf($class, $actual);
        $this->assertSame($actual, $class::instance());
    }

    private function renderParsedContexts($contexts)
    {
        $parsed = array();

        foreach ($contexts as $context) {
            $parsedContext = strval($context);
            $parsedSymbols = array();

            foreach ($context->symbols as $symbol) {
                $parsedSymbols[] = '// ' . $symbol->type . ' ' . $symbol;
            }

            if ($parsedSymbols) {
                if ($parsedContext) {
                    $parsedContext .= "\n";
                }

                $parsedContext .= implode("\n", $parsedSymbols);

                if ($parsedSymbols) {
                    $parsedContext .= "\n";
                }

                $parsed[] = $parsedContext;
            } else {
                $parsed[] = strval($context);
            }
        }

        return trim("<?php\n\n" . implode("\n// end of context\n\n", $parsed));
    }

    private function contextDetails($contexts, $tokens)
    {
        $details = array();

        foreach ($contexts as $context) {
            $contextTokens = array_slice($tokens, $context->tokenOffset, $context->tokenSize);
            $contextString = '';

            foreach ($contextTokens as $token) {
                $contextString .= $token[1];
            }

            $statementDetails = array();

            foreach ($context->useStatements() as $statement) {
                $statementTokens = array_slice($tokens, $statement->tokenOffset, $statement->tokenSize);
                $statementString = '';

                foreach ($statementTokens as $token) {
                    $statementString .= $token[1];
                }

                $statementDetails[] = array(
                    array($statement->line, $statement->column, $statement->offset, $statement->size),
                    $statementString,
                );
            }

            $symbolDetails = array();

            foreach ($context->symbols as $symbol) {
                $symbolTokens = array_slice($tokens, $symbol->tokenOffset, $symbol->tokenSize);
                $symbolString = '';

                foreach ($symbolTokens as $token) {
                    $symbolString .= $token[1];
                }

                $symbolDetails[] = array(
                    array($symbol->line, $symbol->column, $symbol->offset, $symbol->size),
                    $symbolString,
                );
            }

            $details[] = array(
                array($context->line, $context->column, $context->offset, $context->size),
                $contextString,
                $statementDetails,
                $symbolDetails,
            );
        }

        return $details;
    }
}
