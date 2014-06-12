<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution;

use Eloquent\Cosmos\Resolution\Context\Factory\ResolutionContextFactory;
use Eloquent\Cosmos\Resolution\Context\ResolutionContext;
use Eloquent\Cosmos\Symbol\Factory\SymbolFactory;
use Eloquent\Cosmos\Symbol\Symbol;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class FixedContextSymbolResolverTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->factory = new SymbolFactory;
        $this->context = new ResolutionContext($this->factory->create('\VendorA\PackageA'));
        $this->innerResolver = new SymbolResolver;
        $this->resolver = new FixedContextSymbolResolver($this->context, $this->innerResolver);

        $this->contextFactory = ResolutionContextFactory::instance();
    }

    public function testConstructor()
    {
        $this->assertSame($this->context, $this->resolver->context());
        $this->assertSame($this->innerResolver, $this->resolver->resolver());
    }

    public function testConstructorDefaults()
    {
        $this->resolver = new FixedContextSymbolResolver;

        $this->assertEquals(new ResolutionContext, $this->resolver->context());
        $this->assertSame(SymbolResolver::instance(), $this->resolver->resolver());
    }

    public function testResolve()
    {
        $qualified = $this->factory->create('\VendorB\PackageB');
        $reference = $this->factory->create('Symbol');

        $this->assertSame($qualified, $this->resolver->resolve($qualified));
        $this->assertSame('\VendorA\PackageA\Symbol', $this->resolver->resolve($reference)->string());
    }

    public function testFromObject()
    {
        $actual = FixedContextSymbolResolver::fromObject($this);
        $expected =
            new FixedContextSymbolResolver($this->contextFactory->createFromObject($this), $this->innerResolver);

        $this->assertEquals($expected, $actual);
    }

    public function testFromSymbol()
    {
        $symbol = Symbol::fromRuntimeString(__CLASS__);
        $actual = FixedContextSymbolResolver::fromSymbol($symbol);
        $expected =
            new FixedContextSymbolResolver($this->contextFactory->createFromSymbol($symbol), $this->innerResolver);

        $this->assertEquals($expected, $actual);
    }

    public function testFromSymbolWithString()
    {
        $symbol = __CLASS__;
        $actual = FixedContextSymbolResolver::fromSymbol($symbol);
        $expected =
            new FixedContextSymbolResolver($this->contextFactory->createFromSymbol($symbol), $this->innerResolver);

        $this->assertEquals($expected, $actual);
    }

    public function testFromClass()
    {
        $class = new ReflectionClass(__CLASS__);
        $actual = FixedContextSymbolResolver::fromClass($class);
        $expected = new FixedContextSymbolResolver(
            $this->contextFactory->createFromClass($class),
            $this->innerResolver
        );

        $this->assertEquals($expected, $actual);
    }
}
