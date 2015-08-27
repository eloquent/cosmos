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
use PHPUnit_Framework_TestCase;

/**
 * @covers \Eloquent\Cosmos\UseStatement\UseStatement
 */
class UseStatementTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->clauses = array(
            new UseStatementClause(Symbol::fromString('\NamespaceA\SymbolA'), 'SymbolB'),
            new UseStatementClause(Symbol::fromString('\NamespaceB\SymbolC')),
        );
        $this->subject = new UseStatement($this->clauses, 'const');
    }

    public function testFromSymbol()
    {
        $symbol = Symbol::fromString('\NamespaceA\SymbolA');
        $actual = UseStatement::fromSymbol($symbol, 'Alias', 'const');
        $expected = new UseStatement(array(new UseStatementClause($symbol, 'Alias')), 'const');

        $this->assertEquals($expected, $actual);
    }

    public function testConstructor()
    {
        $this->assertEquals($this->clauses, $this->subject->clauses());
        $this->assertSame('const', $this->subject->type());
    }

    public function testToString()
    {
        $this->assertSame('use const NamespaceA\SymbolA as SymbolB, NamespaceB\SymbolC', strval($this->subject));
    }

    public function testToStringSingleNoType()
    {
        $this->clauses = array(new UseStatementClause(Symbol::fromString('\SymbolA')));
        $this->subject = new UseStatement($this->clauses);

        $this->assertSame('use SymbolA', strval($this->subject));
    }
}
