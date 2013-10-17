<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Consumer;

use Phake;
use PHPUnit_Framework_TestCase;

class ClassNameResolverTraitTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        if (!defined('T_TRAIT')) {
            $this->markTestSkipped('Requires trait support');
        }

        $this->consumer = $this->getObjectForTrait(__NAMESPACE__ . '\ClassNameResolverTrait');
    }

    public function testSetClassNameResolver()
    {
        $classNameResolver = Phake::mock('Eloquent\Cosmos\Resolution\ClassNameResolverInterface');
        $this->consumer->setClassNameResolver($classNameResolver);

        $this->assertSame($classNameResolver, $this->consumer->classNameResolver());
    }

    public function testClassNameResolver()
    {
        $classNameResolver = $this->consumer->classNameResolver();

        $this->assertInstanceOf('Eloquent\Cosmos\Resolution\ClassNameResolverInterface', $classNameResolver);
        $this->assertSame($classNameResolver, $this->consumer->classNameResolver());
    }
}
