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

use Eloquent\Cosmos\ClassName\ClassName;
use Eloquent\Cosmos\Resolution\ClassNameResolver;
use Eloquent\Cosmos\Resolution\Context\Factory\ResolutionContextFactory;
use Eloquent\Cosmos\Resolution\Context\ResolutionContext;
use Eloquent\Cosmos\Resolution\FixedContextClassNameResolver;
use Eloquent\Liberator\Liberator;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class FixedContextClassNameResolverFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->resolver = new ClassNameResolver;
        $this->contextFactory = new ResolutionContextFactory;
        $this->factory = new FixedContextClassNameResolverFactory($this->resolver, $this->contextFactory);
    }

    public function testConstructor()
    {
        $this->assertSame($this->resolver, $this->factory->resolver());
        $this->assertSame($this->contextFactory, $this->factory->contextFactory());
    }

    public function testConstructorDefaults()
    {
        $this->factory = new FixedContextClassNameResolverFactory;

        $this->assertSame(ClassNameResolver::instance(), $this->factory->resolver());
        $this->assertSame(ResolutionContextFactory::instance(), $this->factory->contextFactory());
    }

    public function testCreate()
    {
        $context = new ResolutionContext;
        $actual = $this->factory->create($context);
        $expected = new FixedContextClassNameResolver($context, $this->resolver);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateFromObject()
    {
        $actual = $this->factory->createFromObject($this);
        $expected = new FixedContextClassNameResolver($this->contextFactory->createFromObject($this), $this->resolver);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateFromClass()
    {
        $className = ClassName::fromRuntimeString(__CLASS__);
        $actual = $this->factory->createFromClass($className);
        $expected =
            new FixedContextClassNameResolver($this->contextFactory->createFromClass($className), $this->resolver);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateFromReflector()
    {
        $reflector = new ReflectionClass(__CLASS__);
        $actual = $this->factory->createFromReflector($reflector);
        $expected =
            new FixedContextClassNameResolver($this->contextFactory->createFromReflector($reflector), $this->resolver);

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
