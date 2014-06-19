<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Context\Factory;

use Eloquent\Cosmos\Resolution\Context\ResolutionContext;
use Eloquent\Cosmos\Symbol\Factory\SymbolFactory;
use Eloquent\Cosmos\Symbol\Symbol;
use Eloquent\Cosmos\UseStatement\UseStatement;
use Eloquent\Liberator\Liberator;
use PHPUnit_Framework_TestCase;

class ResolutionContextFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->symbolFactory = new SymbolFactory;
        $this->factory = new ResolutionContextFactory($this->symbolFactory);

        $this->primaryNamespace = Symbol::fromString('\VendorA\PackageA');
        $this->useStatements = array(
            UseStatement::create(Symbol::fromString('\VendorB\PackageB')),
            UseStatement::create(Symbol::fromString('\VendorC\PackageC')),
        );
        $this->context = new ResolutionContext($this->primaryNamespace, $this->useStatements, $this->symbolFactory);
    }

    public function testConstructor()
    {
        $this->assertSame($this->symbolFactory, $this->factory->symbolFactory());
    }

    public function testConstructorDefaults()
    {
        $this->factory = new ResolutionContextFactory;

        $this->assertSame(SymbolFactory::instance(), $this->factory->symbolFactory());
    }

    public function testCreate()
    {
        $actual = $this->factory->create($this->primaryNamespace, $this->useStatements);

        $this->assertEquals($this->context, $actual);
    }

    public function testCreateEmpty()
    {
        $actual = $this->factory->createEmpty();

        $this->assertEquals(new ResolutionContext, $actual);
        $this->assertSame($actual, $this->factory->createEmpty());
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
