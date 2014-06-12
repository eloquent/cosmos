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

use Eloquent\Cosmos\Resolution\Context\Factory\ResolutionContextFactory;
use Eloquent\Cosmos\Symbol\Factory\SymbolFactory;
use Eloquent\Cosmos\Symbol\Symbol;
use Eloquent\Cosmos\UseStatement\Factory\UseStatementFactory;
use PHPUnit_Framework_TestCase;

class ResolutionContextGeneratorTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->contextFactory = new ResolutionContextFactory;
        $this->useStatementFactory = new UseStatementFactory;
        $this->symbolFactory = new SymbolFactory;
        $this->generator = new ResolutionContextGenerator(
            3,
            $this->contextFactory,
            $this->useStatementFactory,
            $this->symbolFactory
        );
    }

    public function testConstructor()
    {
        $this->assertSame(3, $this->generator->maxReferenceAtoms());
        $this->assertSame($this->contextFactory, $this->generator->contextFactory());
        $this->assertSame($this->useStatementFactory, $this->generator->useStatementFactory());
        $this->assertSame($this->symbolFactory, $this->generator->symbolFactory());
    }

    public function testConstructorDefaults()
    {
        $this->generator = new ResolutionContextGenerator;

        $this->assertSame(1, $this->generator->maxReferenceAtoms());
        $this->assertSame(ResolutionContextFactory::instance(), $this->generator->contextFactory());
        $this->assertSame(UseStatementFactory::instance(), $this->generator->useStatementFactory());
        $this->assertSame(SymbolFactory::instance(), $this->generator->symbolFactory());
    }

    public function testGenerate()
    {
        $primaryNamespace = $this->symbolFactory->create('\VendorA\PackageA');
        $symbols = array(
            $this->symbolFactory->create('\VendorC\PackageC'),
            $this->symbolFactory->create('\VendorC\PackageC'),
            $this->symbolFactory->create('\VendorB\PackageB'),
            $this->symbolFactory->create('\VendorA\PackageA\Foo'),
            $this->symbolFactory->create('\VendorA\PackageA\Foo\Bar\Baz'),
            $this->symbolFactory->create('\VendorA\PackageA\Foo\Bar\Baz\Doom'),
            $this->symbolFactory->create('\Foo\Bar\Baz\Qux'),
            $this->symbolFactory->create('\Doom\Bar\Baz\Qux'),
            $this->symbolFactory->create('\Bar\Baz\Qux'),
            $this->symbolFactory->create('\Bar\Baz\Qux'),
            $this->symbolFactory->create('\Foo'),
        );
        $context = $this->generator->generate($symbols, $primaryNamespace);
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
        $symbols = array(
            $this->symbolFactory->create('\VendorC\PackageC'),
            $this->symbolFactory->create('\VendorC\PackageC'),
            $this->symbolFactory->create('\VendorB\PackageB'),
            $this->symbolFactory->create('\VendorA\PackageA\Foo'),
            $this->symbolFactory->create('\VendorA\PackageA\Foo\Bar\Baz'),
            $this->symbolFactory->create('\VendorA\PackageA\Foo\Bar\Baz\Doom'),
            $this->symbolFactory->create('\Foo\Bar\Baz\Qux'),
            $this->symbolFactory->create('\Doom\Bar\Baz\Qux'),
            $this->symbolFactory->create('\Bar\Baz\Qux'),
            $this->symbolFactory->create('\Bar\Baz\Qux'),
            $this->symbolFactory->create('\Foo'),
        );
        $context = $this->generator->generate($symbols);
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
        $this->assertEquals(Symbol::globalNamespace(), $context->primaryNamespace());
    }
}
