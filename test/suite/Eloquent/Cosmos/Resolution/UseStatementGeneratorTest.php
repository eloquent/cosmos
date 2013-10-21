<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution;

use Eloquent\Cosmos\ClassName\Factory\ClassNameFactory;
use PHPUnit_Framework_TestCase;

class UseStatementGeneratorTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->factory = new ClassNameFactory;
        $this->generator = new UseStatementGenerator(3, $this->factory);
    }

    public function testConstructor()
    {
        $this->assertSame(3, $this->generator->maxReferenceAtoms());
        $this->assertSame($this->factory, $this->generator->factory());
    }

    public function testConstructorDefaults()
    {
        $this->generator = new UseStatementGenerator;

        $this->assertSame(2, $this->generator->maxReferenceAtoms());
        $this->assertEquals($this->factory, $this->generator->factory());
    }

    public function testGenerate()
    {
        $primaryNamespace = $this->factory->create('\VendorA\PackageA');
        $classNames = array(
            $this->factory->create('\VendorC\PackageC'),
            $this->factory->create('\VendorC\PackageC'),
            $this->factory->create('\VendorB\PackageB'),
            $this->factory->create('\VendorA\PackageA\Foo'),
            $this->factory->create('\VendorA\PackageA\Foo\Bar\Baz'),
            $this->factory->create('\VendorA\PackageA\Foo\Bar\Baz\Doom'),
            $this->factory->create('\Foo\Bar\Baz\Qux'),
            $this->factory->create('\Doom\Bar\Baz\Qux'),
            $this->factory->create('\Bar\Baz\Qux'),
            $this->factory->create('\Bar\Baz\Qux'),
            $this->factory->create('\Foo'),
        );
        $useStatements = $this->generator->generate($classNames, $primaryNamespace);
        $actual = array();
        foreach ($useStatements as $useStatement) {
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
    }

    public function testGenerateDefaultNamespace()
    {
        $classNames = array(
            $this->factory->create('\VendorC\PackageC'),
            $this->factory->create('\VendorC\PackageC'),
            $this->factory->create('\VendorB\PackageB'),
            $this->factory->create('\VendorA\PackageA\Foo'),
            $this->factory->create('\VendorA\PackageA\Foo\Bar\Baz'),
            $this->factory->create('\VendorA\PackageA\Foo\Bar\Baz\Doom'),
            $this->factory->create('\Foo\Bar\Baz\Qux'),
            $this->factory->create('\Doom\Bar\Baz\Qux'),
            $this->factory->create('\Bar\Baz\Qux'),
            $this->factory->create('\Bar\Baz\Qux'),
            $this->factory->create('\Foo'),
        );
        $useStatements = $this->generator->generate($classNames);
        $actual = array();
        foreach ($useStatements as $useStatement) {
            $actual[] = $useStatement->string();
        }
        $expected = array(
            'use Doom\Bar\Baz\Qux as DoomBarBazQux',
            'use Foo\Bar\Baz\Qux as FooBarBazQux',
            'use VendorA\PackageA\Foo\Bar\Baz',
            'use VendorA\PackageA\Foo\Bar\Baz\Doom',
        );

        $this->assertSame($expected, $actual);
    }
}
