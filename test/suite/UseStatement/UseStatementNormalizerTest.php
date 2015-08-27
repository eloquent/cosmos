<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\UseStatement;

use Eloquent\Cosmos\Symbol\Symbol;
use Eloquent\Liberator\Liberator;
use PHPUnit_Framework_TestCase;

class UseStatementNormalizerTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->useStatementFactory = new UseStatementFactory();
        $this->normalizer = new UseStatementNormalizer($this->useStatementFactory);
    }

    public function testNormalizeStatements()
    {
        $useStatements = array(
            new UseStatement(
                array(
                    new UseStatementClause(Symbol::fromString('\SymbolB')),
                    new UseStatementClause(Symbol::fromString('\SymbolA')),
                    new UseStatementClause(Symbol::fromString('\SymbolB'), 'SymbolC'),
                    new UseStatementClause(Symbol::fromString('\SymbolD')),
                ),
                'function'
            ),
            new UseStatement(
                array(
                    new UseStatementClause(Symbol::fromString('\SymbolE')),
                    new UseStatementClause(Symbol::fromString('\SymbolA')),
                ),
                'function'
            ),
            new UseStatement(
                array(
                    new UseStatementClause(Symbol::fromString('\SymbolB')),
                    new UseStatementClause(Symbol::fromString('\SymbolA')),
                    new UseStatementClause(Symbol::fromString('\SymbolB'), 'SymbolC'),
                    new UseStatementClause(Symbol::fromString('\SymbolD')),
                )
            ),
            new UseStatement(
                array(
                    new UseStatementClause(Symbol::fromString('\SymbolE')),
                    new UseStatementClause(Symbol::fromString('\SymbolA')),
                )
            ),
            new UseStatement(
                array(
                    new UseStatementClause(Symbol::fromString('\SymbolB')),
                    new UseStatementClause(Symbol::fromString('\SymbolA')),
                    new UseStatementClause(Symbol::fromString('\SymbolB'), 'SymbolC'),
                    new UseStatementClause(Symbol::fromString('\SymbolD')),
                ),
                'const'
            ),
            new UseStatement(
                array(
                    new UseStatementClause(Symbol::fromString('\SymbolE')),
                    new UseStatementClause(Symbol::fromString('\SymbolA')),
                ),
                'const'
            ),
        );
        $actual = $this->normalizer->normalizeStatements($useStatements);
        $actual = implode("\n", array_map('strval', $actual));
        $expected = <<<'EOD'
use SymbolA
use SymbolB
use SymbolB as SymbolC
use SymbolD
use SymbolE
use const SymbolA
use const SymbolB
use const SymbolB as SymbolC
use const SymbolD
use const SymbolE
use function SymbolA
use function SymbolB
use function SymbolB as SymbolC
use function SymbolD
use function SymbolE
EOD;

        $this->assertSame($expected, $actual);
    }

    public function testInstance()
    {
        $class = get_class($this->normalizer);
        $liberatedClass = Liberator::liberateClass($class);
        $liberatedClass->instance = null;
        $actual = $class::instance();

        $this->assertInstanceOf($class, $actual);
        $this->assertSame($actual, $class::instance());
    }
}
