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

use Eloquent\Cosmos\ClassName\Factory\ClassNameFactory;
use PHPUnit_Framework_TestCase;

class UseStatementTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->factory = new ClassNameFactory;
        $this->className = $this->factory->create('\Namespace\Class');
        $this->alias = $this->factory->create('Alias');
        $this->useStatement = new UseStatement($this->className, $this->alias);
    }

    public function testConstructor()
    {
        $this->assertEquals($this->className, $this->useStatement->className());
        $this->assertEquals($this->alias, $this->useStatement->alias());
    }

    public function testConstructorDefaults()
    {
        $this->useStatement = new UseStatement($this->className);

        $this->assertNull($this->useStatement->alias());
    }

    public function testConstructorFailureInvalidAliasMultipleAtoms()
    {
        $this->alias = $this->factory->create('Namespace\Alias');

        $this->setExpectedException('Eloquent\Cosmos\ClassName\Exception\InvalidClassNameAtomException');
        new UseStatement($this->className, $this->alias);
    }

    public function testConstructorFailureInvalidAliasSelfAtom()
    {
        $this->alias = $this->factory->create('.');

        $this->setExpectedException('Eloquent\Cosmos\ClassName\Exception\InvalidClassNameAtomException');
        new UseStatement($this->className, $this->alias);
    }

    public function testConstructorFailureInvalidAliasParentAtom()
    {
        $this->alias = $this->factory->create('..');

        $this->setExpectedException('Eloquent\Cosmos\ClassName\Exception\InvalidClassNameAtomException');
        new UseStatement($this->className, $this->alias);
    }

    public function testEffectiveAlias()
    {
        $this->assertSame('Alias', $this->useStatement->effectiveAlias()->string());
    }

    public function testEffectiveAliasNoAlias()
    {
        $this->useStatement = new UseStatement($this->className);

        $this->assertSame('Class', $this->useStatement->effectiveAlias()->string());
    }

    public function testString()
    {
        $this->assertSame('use Namespace\Class as Alias', $this->useStatement->string());
        $this->assertSame('use Namespace\Class as Alias', strval($this->useStatement));
    }

    public function testStringNoAlias()
    {
        $this->useStatement = new UseStatement($this->className);

        $this->assertSame('use Namespace\Class', $this->useStatement->string());
        $this->assertSame('use Namespace\Class', strval($this->useStatement));
    }
}