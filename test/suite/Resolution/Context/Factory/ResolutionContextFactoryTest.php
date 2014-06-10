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

use Eloquent\Cosmos\ClassName\Factory\ClassNameFactory;
use Eloquent\Cosmos\Resolution\Context\ResolutionContext;
use Eloquent\Cosmos\UseStatement\UseStatement;
use Eloquent\Liberator\Liberator;
use PHPUnit_Framework_TestCase;

class ResolutionContextFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->classNameFactory = new ClassNameFactory;
        $this->factory = new ResolutionContextFactory($this->classNameFactory);

        $this->primaryNamespace = $this->classNameFactory->create('\VendorA\PackageA');
        $this->useStatements = array(
            new UseStatement($this->classNameFactory->create('\VendorB\PackageB')),
            new UseStatement($this->classNameFactory->create('\VendorC\PackageC')),
        );
    }

    public function testConstructor()
    {
        $this->assertSame($this->classNameFactory, $this->factory->classNameFactory());
    }

    public function testConstructorDefaults()
    {
        $this->factory = new ResolutionContextFactory;

        $this->assertSame(ClassNameFactory::instance(), $this->factory->classNameFactory());
    }

    public function testCreate()
    {
        $actual = $this->factory->create($this->primaryNamespace, $this->useStatements);
        $expected = new ResolutionContext($this->primaryNamespace, $this->useStatements, $this->classNameFactory);

        $this->assertEquals($expected, $actual);
    }

    public function testInstance()
    {
        $class = Liberator::liberateClass(__NAMESPACE__ . '\ResolutionContextFactory');
        $class->instance = null;
        $actual = ResolutionContextFactory::instance();

        $this->assertInstanceOf(__NAMESPACE__ . '\ResolutionContextFactory', $actual);
        $this->assertSame($actual, ResolutionContextFactory::instance());
    }
}
