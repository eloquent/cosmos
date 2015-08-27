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
use Phake;
use PHPUnit_Framework_TestCase;

class UseStatementTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->clauses = array(
            new UseStatementClause(Symbol::fromString('\NamespaceA\SymbolA'), Symbol::fromString('SymbolB')),
            new UseStatementClause(Symbol::fromString('\NamespaceB\SymbolC')),
        );
        $this->useStatement = new UseStatement($this->clauses, UseStatementType::CONSTANT());
    }

    public function testConstructor()
    {
        $this->assertEquals($this->clauses, $this->useStatement->clauses());
        $this->assertSame(UseStatementType::CONSTANT(), $this->useStatement->type());
    }

    public function testConstructorDefaults()
    {
        $this->useStatement = new UseStatement($this->clauses);

        $this->assertSame(UseStatementType::TYPE(), $this->useStatement->type());
    }

    public function testCreate()
    {
        $actual = UseStatement::create(
            Symbol::fromString('\Symbol'),
            Symbol::fromString('Alias'),
            UseStatementType::CONSTANT()
        );
        $expected = new UseStatement(
            array(new UseStatementClause(Symbol::fromString('\Symbol'), Symbol::fromString('Alias'))),
            UseStatementType::CONSTANT()
        );

        $this->assertEquals($expected, $actual);
    }

    public function testFromClause()
    {
        $actual = UseStatement::fromClause($this->clauses[0], UseStatementType::CONSTANT());
        $expected = new UseStatement(array($this->clauses[0]), UseStatementType::CONSTANT());

        $this->assertEquals($expected, $actual);
    }

    public function testFromClauses()
    {
        $actual = UseStatement::fromClauses($this->clauses, UseStatementType::CONSTANT());
        $expected = array(
            new UseStatement(array($this->clauses[0]), UseStatementType::CONSTANT()),
            new UseStatement(array($this->clauses[1]), UseStatementType::CONSTANT()),
        );

        $this->assertEquals($expected, $actual);
    }

    public function testConstructorFailureEmpty()
    {
        $this->setExpectedException('Eloquent\Cosmos\UseStatement\Exception\EmptyUseStatementException');
        new UseStatement(array());
    }

    public function testString()
    {
        $this->assertSame('use const NamespaceA\SymbolA as SymbolB, NamespaceB\SymbolC', $this->useStatement->string());
        $this->assertSame('use const NamespaceA\SymbolA as SymbolB, NamespaceB\SymbolC', strval($this->useStatement));
    }

    public function testStringSingleNoType()
    {
        $this->clauses = array(new UseStatementClause(Symbol::fromString('\SymbolA')));
        $this->useStatement = new UseStatement($this->clauses);
        $this->assertSame('use SymbolA', $this->useStatement->string());
        $this->assertSame('use SymbolA', strval($this->useStatement));
    }

    public function testAccept()
    {
        $visitor = Phake::mock('Eloquent\Cosmos\Resolution\Context\ResolutionContextVisitorInterface');
        $this->useStatement->accept($visitor);

        Phake::verify($visitor)->visitUseStatement($this->identicalTo($this->useStatement));
    }
}
