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

use Eloquent\Cosmos\Symbol\Factory\SymbolFactory;
use PHPUnit_Framework_TestCase;
use Phake;

class UseStatementTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->factory = new SymbolFactory;
        $this->symbol = $this->factory->create('\Namespace\Symbol');
        $this->alias = $this->factory->create('Alias');
        $this->useStatement = new UseStatement($this->symbol, $this->alias, UseStatementType::CONSTANT());
    }

    public function testConstructor()
    {
        $this->assertEquals($this->symbol, $this->useStatement->symbol());
        $this->assertEquals($this->alias, $this->useStatement->alias());
        $this->assertSame(UseStatementType::CONSTANT(), $this->useStatement->type());
    }

    public function testConstructorDefaults()
    {
        $this->useStatement = new UseStatement($this->symbol);

        $this->assertNull($this->useStatement->alias());
        $this->assertSame(UseStatementType::TYPE(), $this->useStatement->type());
    }

    public function testConstructorFailureInvalidAliasMultipleAtoms()
    {
        $this->alias = $this->factory->create('Namespace\Alias');

        $this->setExpectedException('Eloquent\Cosmos\Symbol\Exception\InvalidSymbolAtomException');
        new UseStatement($this->symbol, $this->alias);
    }

    public function testConstructorFailureInvalidAliasSelfAtom()
    {
        $this->alias = $this->factory->create('.');

        $this->setExpectedException('Eloquent\Cosmos\Symbol\Exception\InvalidSymbolAtomException');
        new UseStatement($this->symbol, $this->alias);
    }

    public function testConstructorFailureInvalidAliasParentAtom()
    {
        $this->alias = $this->factory->create('..');

        $this->setExpectedException('Eloquent\Cosmos\Symbol\Exception\InvalidSymbolAtomException');
        new UseStatement($this->symbol, $this->alias);
    }

    public function testEffectiveAlias()
    {
        $this->assertSame('Alias', $this->useStatement->effectiveAlias()->string());
    }

    public function testEffectiveAliasNoAlias()
    {
        $this->useStatement = new UseStatement($this->symbol);

        $this->assertSame('Symbol', $this->useStatement->effectiveAlias()->string());
    }

    public function testString()
    {
        $this->assertSame('use Namespace\Symbol as Alias', $this->useStatement->string());
        $this->assertSame('use Namespace\Symbol as Alias', strval($this->useStatement));
    }

    public function testStringNoAlias()
    {
        $this->useStatement = new UseStatement($this->symbol);

        $this->assertSame('use Namespace\Symbol', $this->useStatement->string());
        $this->assertSame('use Namespace\Symbol', strval($this->useStatement));
    }

    public function testAccept()
    {
        $visitor = Phake::mock('Eloquent\Cosmos\Resolution\Context\ResolutionContextVisitorInterface');
        $this->useStatement->accept($visitor);

        Phake::verify($visitor)->visitUseStatement($this->identicalTo($this->useStatement));
    }
}
