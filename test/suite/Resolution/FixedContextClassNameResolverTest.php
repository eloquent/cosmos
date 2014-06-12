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

use Eloquent\Cosmos\ClassName\ClassName;
use Eloquent\Cosmos\ClassName\Factory\ClassNameFactory;
use Eloquent\Cosmos\Resolution\Context\Factory\ResolutionContextFactory;
use Eloquent\Cosmos\Resolution\Context\ResolutionContext;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class FixedContextClassNameResolverTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->factory = new ClassNameFactory;
        $this->context = new ResolutionContext($this->factory->create('\VendorA\PackageA'));
        $this->innerResolver = new ClassNameResolver;
        $this->resolver = new FixedContextClassNameResolver($this->context, $this->innerResolver);

        $this->contextFactory = ResolutionContextFactory::instance();
    }

    public function testConstructor()
    {
        $this->assertSame($this->context, $this->resolver->context());
        $this->assertSame($this->innerResolver, $this->resolver->resolver());
    }

    public function testConstructorDefaults()
    {
        $this->resolver = new FixedContextClassNameResolver;

        $this->assertEquals(new ResolutionContext, $this->resolver->context());
        $this->assertSame(ClassNameResolver::instance(), $this->resolver->resolver());
    }

    public function testResolve()
    {
        $qualified = $this->factory->create('\VendorB\PackageB');
        $reference = $this->factory->create('Class');

        $this->assertSame($qualified, $this->resolver->resolve($qualified));
        $this->assertSame('\VendorA\PackageA\Class', $this->resolver->resolve($reference)->string());
    }

    public function testFromObject()
    {
        $actual = FixedContextClassNameResolver::fromObject($this);
        $expected =
            new FixedContextClassNameResolver($this->contextFactory->createFromObject($this), $this->innerResolver);

        $this->assertEquals($expected, $actual);
    }

    public function testFromClass()
    {
        $className = ClassName::fromRuntimeString(__CLASS__);
        $actual = FixedContextClassNameResolver::fromClass($className);
        $expected =
            new FixedContextClassNameResolver($this->contextFactory->createFromClass($className), $this->innerResolver);

        $this->assertEquals($expected, $actual);
    }

    public function testFromReflector()
    {
        $reflector = new ReflectionClass(__CLASS__);
        $actual = FixedContextClassNameResolver::fromReflector($reflector);
        $expected = new FixedContextClassNameResolver(
            $this->contextFactory->createFromReflector($reflector),
            $this->innerResolver
        );

        $this->assertEquals($expected, $actual);
    }
}
