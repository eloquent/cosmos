<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Context\Generator;

use Eloquent\Cosmos\ClassName\ClassName;
use Eloquent\Cosmos\ClassName\Factory\ClassNameFactory;
use Eloquent\Cosmos\Resolution\Context\Factory\ResolutionContextFactory;
use Eloquent\Cosmos\UseStatement\Factory\UseStatementFactory;
use PHPUnit_Framework_TestCase;

class ResolutionContextGeneratorTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->contextFactory = new ResolutionContextFactory;
        $this->useStatementFactory = new UseStatementFactory;
        $this->classNameFactory = new ClassNameFactory;
        $this->generator = new ResolutionContextGenerator(
            3,
            $this->contextFactory,
            $this->useStatementFactory,
            $this->classNameFactory
        );
    }

    public function testConstructor()
    {
        $this->assertSame(3, $this->generator->maxReferenceAtoms());
        $this->assertSame($this->contextFactory, $this->generator->contextFactory());
        $this->assertSame($this->useStatementFactory, $this->generator->useStatementFactory());
        $this->assertSame($this->classNameFactory, $this->generator->classNameFactory());
    }

    public function testConstructorDefaults()
    {
        $this->generator = new ResolutionContextGenerator;

        $this->assertSame(1, $this->generator->maxReferenceAtoms());
        $this->assertSame(ResolutionContextFactory::instance(), $this->generator->contextFactory());
        $this->assertSame(UseStatementFactory::instance(), $this->generator->useStatementFactory());
        $this->assertSame(ClassNameFactory::instance(), $this->generator->classNameFactory());
    }

    public function testGenerate()
    {
        $primaryNamespace = $this->classNameFactory->create('\VendorA\PackageA');
        $classNames = array(
            $this->classNameFactory->create('\VendorC\PackageC'),
            $this->classNameFactory->create('\VendorC\PackageC'),
            $this->classNameFactory->create('\VendorB\PackageB'),
            $this->classNameFactory->create('\VendorA\PackageA\Foo'),
            $this->classNameFactory->create('\VendorA\PackageA\Foo\Bar\Baz'),
            $this->classNameFactory->create('\VendorA\PackageA\Foo\Bar\Baz\Doom'),
            $this->classNameFactory->create('\Foo\Bar\Baz\Qux'),
            $this->classNameFactory->create('\Doom\Bar\Baz\Qux'),
            $this->classNameFactory->create('\Bar\Baz\Qux'),
            $this->classNameFactory->create('\Bar\Baz\Qux'),
            $this->classNameFactory->create('\Foo'),
        );
        $context = $this->generator->generate($classNames, $primaryNamespace);
        $actual = array();
        foreach ($context->useStatements() as $useStatement) {
            $actual[] = $useStatement->string();
        }
        $expected = array(
            'use Bar\Baz\Qux as BarBazQux',
            'use Doom\Bar\Baz\Qux as DoomBarBazQux',
            'use Foo',
            'use Foo\Bar\Baz\Qux as FooBarBazQux',
            'use VendorA\PackageA\Foo\Bar\Baz\Doom',
            'use VendorB\PackageB',
            'use VendorC\PackageC',
        );

        $this->assertSame($expected, $actual);
        $this->assertEquals($primaryNamespace, $context->primaryNamespace());
    }

    public function testGenerateDefaultNamespace()
    {
        $classNames = array(
            $this->classNameFactory->create('\VendorC\PackageC'),
            $this->classNameFactory->create('\VendorC\PackageC'),
            $this->classNameFactory->create('\VendorB\PackageB'),
            $this->classNameFactory->create('\VendorA\PackageA\Foo'),
            $this->classNameFactory->create('\VendorA\PackageA\Foo\Bar\Baz'),
            $this->classNameFactory->create('\VendorA\PackageA\Foo\Bar\Baz\Doom'),
            $this->classNameFactory->create('\Foo\Bar\Baz\Qux'),
            $this->classNameFactory->create('\Doom\Bar\Baz\Qux'),
            $this->classNameFactory->create('\Bar\Baz\Qux'),
            $this->classNameFactory->create('\Bar\Baz\Qux'),
            $this->classNameFactory->create('\Foo'),
        );
        $context = $this->generator->generate($classNames);
        $actual = array();
        foreach ($context->useStatements() as $useStatement) {
            $actual[] = $useStatement->string();
        }
        $expected = array(
            'use Doom\Bar\Baz\Qux as DoomBarBazQux',
            'use Foo\Bar\Baz\Qux as FooBarBazQux',
            'use VendorA\PackageA\Foo\Bar\Baz',
            'use VendorA\PackageA\Foo\Bar\Baz\Doom',
        );

        $this->assertSame($expected, $actual);
        $this->assertEquals(ClassName::globalNamespace(), $context->primaryNamespace());
    }
}
