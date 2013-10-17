<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Cosmos\ClassName\Resolver;

use Eloquent\Cosmos\ClassName\Factory\ClassNameFactory;
use Eloquent\Cosmos\ClassName\ResolutionContext;
use PHPUnit_Framework_TestCase;

class BoundClassNameResolverTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->factory = new ClassNameFactory;
        $this->context = new ResolutionContext($this->factory->create('\VendorA\PackageA'));
        $this->innerResolver = new ClassNameResolver;
        $this->resolver = new BoundClassNameResolver($this->context, $this->innerResolver);
    }

    public function testConstructor()
    {
        $this->assertSame($this->context, $this->resolver->context());
        $this->assertSame($this->innerResolver, $this->resolver->resolver());
    }

    public function testConstructorDefaults()
    {
        $this->resolver = new BoundClassNameResolver;

        $this->assertEquals(new ResolutionContext, $this->resolver->context());
        $this->assertEquals($this->innerResolver, $this->resolver->resolver());
    }

    public function testResolve()
    {
        $qualified = $this->factory->create('\VendorB\PackageB');
        $reference = $this->factory->create('Class');

        $this->assertSame($qualified, $this->resolver->resolve($qualified));
        $this->assertSame('\VendorA\PackageA\Class', $this->resolver->resolve($reference)->string());
    }
}
