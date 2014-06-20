<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Context\Parser\Element;

use Eloquent\Cosmos\Resolution\Context\Parser\ParserPosition;
use Eloquent\Cosmos\Symbol\Symbol;
use Eloquent\Cosmos\UseStatement\UseStatement;
use Eloquent\Cosmos\UseStatement\UseStatementClause;
use Eloquent\Cosmos\UseStatement\UseStatementType;
use PHPUnit_Framework_TestCase;
use Phake;

/**
 * @covers \Eloquent\Cosmos\Resolution\Context\Parser\Element\ParsedUseStatement
 * @covers \Eloquent\Cosmos\Resolution\Context\Parser\Element\AbstractParsedElement
 */
class ParsedUseStatementTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->clauses = array(
            new UseStatementClause(Symbol::fromString('\NamespaceA\SymbolA'), Symbol::fromString('SymbolB')),
            new UseStatementClause(Symbol::fromString('\NamespaceB\SymbolC')),
        );
        $this->innerUseStatement = new UseStatement($this->clauses, UseStatementType::CONSTANT());
        $this->position = new ParserPosition(111, 222);
        $this->useStatement = new ParsedUseStatement($this->innerUseStatement, $this->position, 333, 444);
    }

    public function testConstructor()
    {
        $this->assertSame($this->innerUseStatement, $this->useStatement->useStatement());
        $this->assertSame($this->position, $this->useStatement->position());
        $this->assertSame(333, $this->useStatement->offset());
        $this->assertSame(444, $this->useStatement->size());
        $this->assertSame($this->innerUseStatement->clauses(), $this->useStatement->clauses());
        $this->assertSame($this->innerUseStatement->type(), $this->useStatement->type());
        $this->assertSame($this->innerUseStatement->string(), $this->useStatement->string());
        $this->assertSame(strval($this->innerUseStatement), strval($this->useStatement));
    }

    public function testConstructorDefaults()
    {
        $this->useStatement = new ParsedUseStatement($this->innerUseStatement);

        $this->assertEquals(new ParserPosition(0, 0), $this->useStatement->position());
        $this->assertSame(0, $this->useStatement->offset());
        $this->assertSame(0, $this->useStatement->size());
    }

    public function testAccept()
    {
        $visitor = Phake::mock('Eloquent\Cosmos\Resolution\Context\ResolutionContextVisitorInterface');
        $this->useStatement->accept($visitor);

        Phake::verify($visitor)->visitUseStatement($this->identicalTo($this->useStatement));
    }
}
