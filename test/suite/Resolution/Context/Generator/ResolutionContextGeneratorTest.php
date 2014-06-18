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
use Eloquent\Cosmos\Resolution\Context\Renderer\ResolutionContextRenderer;
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

        $this->contextRenderer = ResolutionContextRenderer::instance();
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
        $context = $this->generator->generate($primaryNamespace, $symbols, $symbols, $symbols);
        $actual = $this->contextRenderer->renderContext($context);
        $expected = <<<'EOD'
namespace VendorA\PackageA;

use Bar\Baz\Qux as BarBazQux;
use Doom\Bar\Baz\Qux as DoomBarBazQux;
use Foo;
use Foo\Bar\Baz\Qux as FooBarBazQux;
use VendorA\PackageA\Foo\Bar\Baz\Doom;
use VendorB\PackageB;
use VendorC\PackageC;
use function Bar\Baz\Qux as BarBazQux;
use function Doom\Bar\Baz\Qux as DoomBarBazQux;
use function Foo;
use function Foo\Bar\Baz\Qux as FooBarBazQux;
use function VendorA\PackageA\Foo\Bar\Baz\Doom;
use function VendorB\PackageB;
use function VendorC\PackageC;
use const Bar\Baz\Qux as BarBazQux;
use const Doom\Bar\Baz\Qux as DoomBarBazQux;
use const Foo;
use const Foo\Bar\Baz\Qux as FooBarBazQux;
use const VendorA\PackageA\Foo\Bar\Baz\Doom;
use const VendorB\PackageB;
use const VendorC\PackageC;

EOD;

        $this->assertSame($expected, $actual);
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
        $context = $this->generator->generate(null, $symbols, $symbols, $symbols);
        $actual = $this->contextRenderer->renderContext($context);
        $expected = <<<'EOD'
use Doom\Bar\Baz\Qux as DoomBarBazQux;
use Foo\Bar\Baz\Qux as FooBarBazQux;
use VendorA\PackageA\Foo\Bar\Baz;
use VendorA\PackageA\Foo\Bar\Baz\Doom;
use function Doom\Bar\Baz\Qux as DoomBarBazQux;
use function Foo\Bar\Baz\Qux as FooBarBazQux;
use function VendorA\PackageA\Foo\Bar\Baz;
use function VendorA\PackageA\Foo\Bar\Baz\Doom;
use const Doom\Bar\Baz\Qux as DoomBarBazQux;
use const Foo\Bar\Baz\Qux as FooBarBazQux;
use const VendorA\PackageA\Foo\Bar\Baz;
use const VendorA\PackageA\Foo\Bar\Baz\Doom;

EOD;

        $this->assertSame($expected, $actual);
    }
}
