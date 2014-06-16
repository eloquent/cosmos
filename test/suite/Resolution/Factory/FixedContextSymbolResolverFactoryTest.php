<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Factory;

use Eloquent\Cosmos\Resolution\Context\Factory\ResolutionContextFactory;
use Eloquent\Cosmos\Resolution\Context\ResolutionContext;
use Eloquent\Cosmos\Resolution\FixedContextSymbolResolver;
use Eloquent\Cosmos\Resolution\SymbolResolver;
use Eloquent\Cosmos\Symbol\Symbol;
use Eloquent\Liberator\Liberator;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionFunction;

class FixedContextSymbolResolverFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->resolver = new SymbolResolver;
        $this->contextFactory = new ResolutionContextFactory;
        $this->factory = new FixedContextSymbolResolverFactory($this->resolver, $this->contextFactory);
    }

    public function testConstructor()
    {
        $this->assertSame($this->resolver, $this->factory->resolver());
        $this->assertSame($this->contextFactory, $this->factory->contextFactory());
    }

    public function testConstructorDefaults()
    {
        $this->factory = new FixedContextSymbolResolverFactory;

        $this->assertSame(SymbolResolver::instance(), $this->factory->resolver());
        $this->assertSame(ResolutionContextFactory::instance(), $this->factory->contextFactory());
    }

    public function testCreate()
    {
        $context = new ResolutionContext;
        $actual = $this->factory->create($context);
        $expected = new FixedContextSymbolResolver($context, $this->resolver);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateFromObject()
    {
        $actual = $this->factory->createFromObject($this);
        $expected = new FixedContextSymbolResolver($this->contextFactory->createFromObject($this), $this->resolver);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateFromSymbol()
    {
        $symbol = Symbol::fromRuntimeString(__CLASS__);
        $actual = $this->factory->createFromSymbol($symbol);
        $expected = new FixedContextSymbolResolver($this->contextFactory->createFromSymbol($symbol), $this->resolver);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateFromFunctionSymbol()
    {
        $symbol = Symbol::fromRuntimeString('\printf');
        $actual = $this->factory->createFromFunctionSymbol($symbol);
        $expected = new FixedContextSymbolResolver(
            $this->contextFactory->createFromFunctionSymbol($symbol),
            $this->resolver
        );

        $this->assertEquals($expected, $actual);
    }

    public function testCreateFromClass()
    {
        $class = new ReflectionClass(__CLASS__);
        $actual = $this->factory->createFromClass($class);
        $expected = new FixedContextSymbolResolver($this->contextFactory->createFromClass($class), $this->resolver);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateFromFunction()
    {
        $function = new ReflectionFunction('\printf');
        $actual = $this->factory->createFromFunction($function);
        $expected = new FixedContextSymbolResolver(
            $this->contextFactory->createFromFunction($function),
            $this->resolver
        );

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
