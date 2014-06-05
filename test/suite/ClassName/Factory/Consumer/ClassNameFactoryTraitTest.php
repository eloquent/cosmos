<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\ClassName\Factory\Consumer;

use Phake;
use PHPUnit_Framework_TestCase;

class ClassNameFactoryTraitTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        if (!defined('T_TRAIT')) {
            $this->markTestSkipped('Requires trait support.');
        }

        $this->consumer = $this->getObjectForTrait(__NAMESPACE__ . '\ClassNameFactoryTrait');
    }

    public function testSetClassNameFactory()
    {
        $classNameFactory = Phake::mock('Eloquent\Cosmos\ClassName\Factory\ClassNameFactoryInterface');
        $this->consumer->setClassNameFactory($classNameFactory);

        $this->assertSame($classNameFactory, $this->consumer->classNameFactory());
    }

    public function testClassNameFactory()
    {
        $classNameFactory = $this->consumer->classNameFactory();

        $this->assertInstanceOf('Eloquent\Cosmos\ClassName\Factory\ClassNameFactoryInterface', $classNameFactory);
        $this->assertSame($classNameFactory, $this->consumer->classNameFactory());
    }
}
