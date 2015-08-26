<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\UseStatement\Normalizer;

use Eloquent\Cosmos\Symbol\Symbol;
use Eloquent\Cosmos\UseStatement\Factory\UseStatementFactory;
use Eloquent\Cosmos\UseStatement\UseStatement;
use Eloquent\Cosmos\UseStatement\UseStatementClause;
use Eloquent\Cosmos\UseStatement\UseStatementType;
use Eloquent\Liberator\Liberator;
use PHPUnit_Framework_TestCase;

class UseStatementNormalizerTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->useStatementFactory = new UseStatementFactory();
        $this->normalizer = new UseStatementNormalizer($this->useStatementFactory);
    }

    public function testConstructor()
    {
        $this->assertEquals($this->useStatementFactory, $this->normalizer->useStatementFactory());
    }

    public function testConstructorDefaults()
    {
        $this->normalizer = new UseStatementNormalizer();

        $this->assertSame(UseStatementFactory::instance(), $this->normalizer->useStatementFactory());
    }

    public function testNormalize()
    {
        $useStatements = array(
            new UseStatement(
                array(
                    new UseStatementClause(Symbol::fromString('\SymbolB')),
                    new UseStatementClause(Symbol::fromString('\SymbolA')),
                    new UseStatementClause(Symbol::fromString('\SymbolB'), Symbol::fromString('SymbolC')),
                    new UseStatementClause(Symbol::fromString('\SymbolD')),
                ),
                UseStatementType::CONSTANT()
            ),
            new UseStatement(
                array(
                    new UseStatementClause(Symbol::fromString('\SymbolE')),
                    new UseStatementClause(Symbol::fromString('\SymbolA')),
                ),
                UseStatementType::CONSTANT()
            ),
            new UseStatement(
                array(
                    new UseStatementClause(Symbol::fromString('\SymbolB')),
                    new UseStatementClause(Symbol::fromString('\SymbolA')),
                    new UseStatementClause(Symbol::fromString('\SymbolB'), Symbol::fromString('SymbolC')),
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
                    new UseStatementClause(Symbol::fromString('\SymbolB'), Symbol::fromString('SymbolC')),
                    new UseStatementClause(Symbol::fromString('\SymbolD')),
                ),
                UseStatementType::FUNCT1ON()
            ),
            new UseStatement(
                array(
                    new UseStatementClause(Symbol::fromString('\SymbolE')),
                    new UseStatementClause(Symbol::fromString('\SymbolA')),
                ),
                UseStatementType::FUNCT1ON()
            ),
        );
        $actual = $this->normalizer->normalize($useStatements);
        $actual = implode("\n", array_map('strval', $actual));
        $expected = <<<'EOD'
use SymbolA
use SymbolB
use SymbolB as SymbolC
use SymbolD
use SymbolE
use function SymbolA
use function SymbolB
use function SymbolB as SymbolC
use function SymbolD
use function SymbolE
use const SymbolA
use const SymbolB
use const SymbolB as SymbolC
use const SymbolD
use const SymbolE
EOD;

        $this->assertEquals($expected, $actual);
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
