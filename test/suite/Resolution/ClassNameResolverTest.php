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

use Eloquent\Cosmos\ClassName\Factory\ClassNameFactory;
use Eloquent\Liberator\Liberator;
use PHPUnit_Framework_TestCase;

class ClassNameResolverTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->contextFactory = new ResolutionContextFactory;
        $this->resolver = new ClassNameResolver($this->contextFactory);

        $this->classNameFactory = new ClassNameFactory;

        $this->primaryNamespace = $this->classNameFactory->create('\VendorA\PackageA');
        $this->context = new ResolutionContext($this->primaryNamespace);
    }

    public function testConstructor()
    {
        $this->assertSame($this->contextFactory, $this->resolver->contextFactory());
    }

    public function testConstructorDefaults()
    {
        $this->resolver = new ClassNameResolver;

        $this->assertSame(ResolutionContextFactory::instance(), $this->resolver->contextFactory());
    }

    public function testResolve()
    {
        $qualified = $this->classNameFactory->create('\VendorB\PackageB');
        $reference = $this->classNameFactory->create('Class');

        $this->assertSame($qualified, $this->resolver->resolve($this->primaryNamespace, $qualified));
        $this->assertSame(
            '\VendorA\PackageA\Class',
            $this->resolver->resolve($this->primaryNamespace, $reference)->string()
        );
    }

    public function testResolveAgainstContext()
    {
        $qualified = $this->classNameFactory->create('\VendorB\PackageB');
        $reference = $this->classNameFactory->create('Class');

        $this->assertSame($qualified, $this->resolver->resolveAgainstContext($this->context, $qualified));
        $this->assertSame(
            '\VendorA\PackageA\Class',
            $this->resolver->resolveAgainstContext($this->context, $reference)->string()
        );
    }

    public function testInstance()
    {
        $class = Liberator::liberateClass(__NAMESPACE__ . '\ClassNameResolver');
        $class->instance = null;
        $actual = ClassNameResolver::instance();

        $this->assertInstanceOf(__NAMESPACE__ . '\ClassNameResolver', $actual);
        $this->assertSame($actual, ClassNameResolver::instance());
    }
}
