<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\UseStatement\Factory;

use Eloquent\Cosmos\Symbol\Symbol;
use Eloquent\Cosmos\UseStatement\UseStatement;
use Eloquent\Cosmos\UseStatement\UseStatementClause;
use Eloquent\Cosmos\UseStatement\UseStatementType;
use Eloquent\Liberator\Liberator;
use PHPUnit_Framework_TestCase;

class UseStatementFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->factory = new UseStatementFactory;

        $this->symbol = Symbol::fromString('\Vendor\Package\Class');
        $this->alias = Symbol::fromString('Alias');
        $this->clauses = array(
            new UseStatementClause(Symbol::fromString('\NamespaceA\SymbolA'), Symbol::fromString('SymbolB')),
            new UseStatementClause(Symbol::fromString('\NamespaceB\SymbolC')),
        );
    }

    public function testCreateClause()
    {
        $actual = $this->factory->createClause($this->symbol, $this->alias);
        $expected = new UseStatementClause($this->symbol, $this->alias);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateStatement()
    {
        $actual = $this->factory->createStatement($this->clauses, UseStatementType::CONSTANT());
        $expected = new UseStatement($this->clauses, UseStatementType::CONSTANT());

        $this->assertEquals($expected, $actual);
    }

    public function testInstance()
    {
        $class = get_class($this->factory);
        $liberatedClass = Liberator::liberateClass($class);
        $liberatedClass->instance = null;
        $actual = $class::instance();

        $this->assertInstanceOf($class, $actual);
        $this->assertSame($actual, $class::instance());
    }
}
