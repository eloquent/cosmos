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
 * @covers \Eloquent\Cosmos\Parser\SymbolParser
 */
class SymbolParserTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->subject = new SymbolParser();

        $this->tokenNormalizer = new TokenNormalizer();
        $this->fixturePath = __DIR__ . '/../../fixture/symbol-parser';
    }

    public function parseTokensData()
    {
        $data = array();

        foreach (scandir(__DIR__ . '/../../fixture/symbol-parser') as $name) {
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
        $isSupported = require $this->fixturePath . '/' . $name . '/supported.php';

        if (!$isSupported) {
            $this->markTestSkipped($message);
        }

        $tokens = $this->tokenNormalizer
            ->normalizeTokens(token_get_all(file_get_contents($this->fixturePath . '/' . $name . '/source.php')));
        $actual = $this->subject->parseTokens($tokens);

        $expectedParsed = file_get_contents($this->fixturePath . '/' . $name . '/parsed');
        $actualParsed = '';

        foreach ($actual as $symbol) {
            $actualParsed .= $symbol[1] . ' ' . $symbol[0] . "\n";
        }

        $expectedDetails = require $this->fixturePath . '/' . $name . '/details.php';
        $details = array();

        foreach ($actual as $symbol) {
            list($symbol) = $symbol;

            $symbolTokens = array_slice($tokens, $symbol->tokenOffset, $symbol->tokenSize);
            $symbolString = '';

            foreach ($symbolTokens as $token) {
                $symbolString .= $token[1];
            }

            $details[] = array(
                array($symbol->line, $symbol->column, $symbol->offset, $symbol->size),
                $symbolString,
            );
        }

        $this->assertSame($expectedParsed, $actualParsed);
        $this->assertEquals($expectedDetails, $details);
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
}
