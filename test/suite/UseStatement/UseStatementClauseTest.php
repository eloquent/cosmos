<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\UseStatement;

use Eloquent\Cosmos\Symbol\Symbol;
use PHPUnit_Framework_TestCase;
use Phake;

class UseStatementClauseTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->symbol = Symbol::fromString('\Namespace\Symbol');
        $this->alias = Symbol::fromString('Alias');
        $this->clause = new UseStatementClause($this->symbol, $this->alias);
    }

    public function testConstructor()
    {
        $this->assertEquals($this->symbol, $this->clause->symbol());
        $this->assertEquals($this->alias, $this->clause->alias());
    }

    public function testConstructorDefaults()
    {
        $this->clause = new UseStatementClause($this->symbol);

        $this->assertNull($this->clause->alias());
    }

    public function testConstructorFailureInvalidAliasMultipleAtoms()
    {
        $this->alias = Symbol::fromString('Namespace\Alias');

        $this->setExpectedException('Eloquent\Cosmos\Symbol\Exception\InvalidSymbolAtomException');
        new UseStatementClause($this->symbol, $this->alias);
    }

    public function testConstructorFailureInvalidAliasSelfAtom()
    {
        $this->alias = Symbol::fromString('.');

        $this->setExpectedException('Eloquent\Cosmos\Symbol\Exception\InvalidSymbolAtomException');
        new UseStatementClause($this->symbol, $this->alias);
    }

    public function testConstructorFailureInvalidAliasParentAtom()
    {
        $this->alias = Symbol::fromString('..');

        $this->setExpectedException('Eloquent\Cosmos\Symbol\Exception\InvalidSymbolAtomException');
        new UseStatementClause($this->symbol, $this->alias);
    }

    public function testEffectiveAlias()
    {
        $this->assertSame('Alias', $this->clause->effectiveAlias()->string());
    }

    public function testEffectiveAliasNoAlias()
    {
        $this->clause = new UseStatementClause($this->symbol);

        $this->assertSame('Symbol', $this->clause->effectiveAlias()->string());
    }

    public function testString()
    {
        $this->assertSame('Namespace\Symbol as Alias', $this->clause->string());
        $this->assertSame('Namespace\Symbol as Alias', strval($this->clause));
    }

    public function testStringNoAlias()
    {
        $this->clause = new UseStatementClause($this->symbol);

        $this->assertSame('Namespace\Symbol', $this->clause->string());
        $this->assertSame('Namespace\Symbol', strval($this->clause));
    }

    public function testAccept()
    {
        $visitor = Phake::mock('Eloquent\Cosmos\Resolution\Context\ResolutionContextVisitorInterface');
        $this->clause->accept($visitor);

        Phake::verify($visitor)->visitUseStatementClause($this->identicalTo($this->clause));
    }
}
