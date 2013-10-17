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

class ClassNameResolverTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->factory = new ClassNameFactory;
        $this->resolver = new ClassNameResolver;

        $this->context = new ResolutionContext($this->factory->create('\VendorA\PackageA'));
    }

    public function testResolve()
    {
        $qualified = $this->factory->create('\VendorB\PackageB');
        $reference = $this->factory->create('Class');

        $this->assertSame($qualified, $this->resolver->resolve($this->context, $qualified));
        $this->assertSame('\VendorA\PackageA\Class', $this->resolver->resolve($this->context, $reference)->string());
    }
}
