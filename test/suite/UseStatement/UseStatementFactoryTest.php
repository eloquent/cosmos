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

/**
 * @covers \Eloquent\Cosmos\UseStatement\UseStatementFactory
 */
class UseStatementFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->subject = new UseStatementFactory();

        $this->symbol = Symbol::fromString('\Vendor\Package\Class');
        $this->alias = Symbol::fromString('Alias');
        $this->clauses = array(
            new UseStatementClause(Symbol::fromString('\NamespaceA\SymbolA'), Symbol::fromString('SymbolB')),
            new UseStatementClause(Symbol::fromString('\NamespaceB\SymbolC')),
        );
    }

    public function testCreateClause()
    {
        $actual = $this->subject->createClause($this->symbol, $this->alias);

        $this->assertEquals(new UseStatementClause($this->symbol, $this->alias), $actual);
    }

    public function testCreateStatement()
    {
        $this->assertEquals(new UseStatement($this->clauses), $this->subject->createStatement($this->clauses));
        $this->assertEquals(
            new UseStatement($this->clauses, 'const'),
            $this->subject->createStatement($this->clauses, 'const')
        );
    }

    public function testCreateStatementFromSymbol()
    {
        $actual = $this->subject->createStatementFromSymbol($this->symbol, $this->alias, 'const');
        $expected = new UseStatement(array(new UseStatementClause($this->symbol, $this->alias)), 'const');

        $this->assertEquals($expected, $actual);
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
