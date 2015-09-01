<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Context\Parser;

use Eloquent\Liberator\Liberator;
use PHPUnit_Framework_TestCase;

/**
 * @covers \Eloquent\Cosmos\Resolution\Context\Parser\ResolutionContextParser
 */
class ResolutionContextParserTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->subject = new ResolutionContextParser();

        $this->tokenNormalizer = new TokenNormalizer();
        $this->fixturePath = __DIR__ . '/../../../../fixture/context-parser';
    }

    public function parseTokensData()
    {
        $data = array();

        foreach (scandir(__DIR__ . '/../../../../fixture/context-parser') as $name) {
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
        $tokens = $this->tokenNormalizer
            ->normalizeTokens(token_get_all(file_get_contents($this->fixturePath . '/' . $name . '/source.php')));
        $actual = $this->subject->parseTokens($tokens);
        $parsed = file_get_contents($this->fixturePath . '/' . $name . '/parsed.php');

        $this->assertSame(trim($parsed), trim("<?php\n\n" . implode("\n//\n\n", $actual)));

        if (is_file($this->fixturePath . '/' . $name . '/assertions.php')) {
            $test = $this;

            require $this->fixturePath . '/' . $name . '/assertions.php';
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
}
