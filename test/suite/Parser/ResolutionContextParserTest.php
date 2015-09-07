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

    public function parseContextsData()
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
     * @dataProvider parseContextsData
     */
    public function testParseContexts($name)
    {
        if (is_file($this->fixturePath . '/' . $name . '/supported.php')) {
            $isSupported = require $this->fixturePath . '/' . $name . '/supported.php';

            if (!$isSupported) {
                $this->markTestSkipped($message);
            }
        }

        $tokens = $this->tokenNormalizer
            ->normalizeTokens(token_get_all(file_get_contents($this->fixturePath . '/' . $name . '/source.php')));
        $actual = $this->subject->parseContexts($tokens);

        $expectedParsed = trim(file_get_contents($this->fixturePath . '/' . $name . '/parsed.php'));
        $expectedDetails = require $this->fixturePath . '/' . $name . '/details.php';

        $this->assertSame($expectedParsed, $this->renderParsedContexts($actual));
        $this->assertEquals($expectedDetails, $this->contextDetails($actual, $tokens));

        foreach ($expectedDetails as $index => $contextDetails) {
            $this->assertSame(strlen($contextDetails[0]), $actual[$index]->size);
        }
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
        $line = 1;
        $offset = 0;
        $previousSize = 0;
        $details = array();

        foreach ($contexts as $context) {
            $contextTokens = array_slice($tokens, $context->tokenOffset, $context->tokenSize);
            $contextSource = '';

            foreach ($contextTokens as $token) {
                $contextSource .= $token[1];
            }

            $contextLineDelta = $context->line - $line;
            $contextOffsetDelta = $context->offset - $offset - $previousSize;
            $line = $context->line;

            $statementDetails = array();

            foreach ($context->useStatements() as $statement) {
                $statementTokens = array_slice($tokens, $statement->tokenOffset, $statement->tokenSize);
                $statementSource = '';

                foreach ($statementTokens as $token) {
                    $statementSource .= $token[1];
                }

                $statementLineDelta = $statement->line - $line;
                $statementOffsetDelta = $statement->offset - $offset - $previousSize;
                $line = $statement->line;
                $offset = $statement->offset;
                $previousSize = $statement->size;

                $statementDetails[] =
                    array($statementSource, $statementLineDelta, $statement->column, $statementOffsetDelta);
            }

            $symbolDetails = array();

            foreach ($context->symbols as $symbol) {
                $symbolTokens = array_slice($tokens, $symbol->tokenOffset, $symbol->tokenSize);
                $symbolSource = '';

                foreach ($symbolTokens as $token) {
                    $symbolSource .= $token[1];
                }

                $symbolLineDelta = $symbol->line - $line;
                $symbolOffsetDelta = $symbol->offset - $offset - $previousSize;
                $line = $symbol->line;
                $offset = $symbol->offset;
                $previousSize = $symbol->size;

                $symbolDetails[] = array($symbolSource, $symbolLineDelta, $symbol->column, $symbolOffsetDelta);
            }

            $details[] = array(
                $contextSource,
                $contextLineDelta,
                $context->column,
                $contextOffsetDelta,
                $statementDetails,
                $symbolDetails,
            );
        }

        return $details;
    }
}
