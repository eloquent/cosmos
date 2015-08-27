<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Context;

use Eloquent\Cosmos\Symbol\Symbol;
use Eloquent\Cosmos\UseStatement\UseStatementFactory;
use Eloquent\Cosmos\UseStatement\UseStatementNormalizer;
use Eloquent\Liberator\Liberator;
use PHPUnit_Framework_TestCase;

class ResolutionContextGeneratorTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->contextFactory = new ResolutionContextFactory();
        $this->useStatementFactory = new UseStatementFactory();
        $this->useStatementNormalizer = new UseStatementNormalizer($this->useStatementFactory);
        $this->subject = new ResolutionContextGenerator(
            $this->contextFactory,
            $this->useStatementFactory,
            $this->useStatementNormalizer
        );
    }

    public function testGenerateContext()
    {
        $primaryNamespace = Symbol::fromString('\VendorA\PackageA');
        $symbols = array(
            Symbol::fromString('\VendorC\PackageC'),
            Symbol::fromString('\VendorC\PackageC'),
            Symbol::fromString('\VendorB\PackageB'),
            Symbol::fromString('\VendorA\PackageA\Foo'),
            Symbol::fromString('\VendorA\PackageA\Foo\Bar\Baz'),
            Symbol::fromString('\VendorA\PackageA\Foo\Bar\Baz\Doom'),
            Symbol::fromString('\Foo\Bar\Baz\Qux'),
            Symbol::fromString('\Doom\Bar\Baz\Qux'),
            Symbol::fromString('\Bar\Baz\Qux'),
            Symbol::fromString('\Bar\Baz\Qux'),
            Symbol::fromString('\Foo'),
        );
        $context = $this->subject
            ->generateContext($primaryNamespace, $symbols, array('function' => $symbols, 'const' => $symbols), 3);
        $actual = strval($context);
        $expected = <<<'EOD'
namespace VendorA\PackageA;

use Bar\Baz\Qux as BarBazQux;
use Doom\Bar\Baz\Qux as DoomBarBazQux;
use Foo;
use Foo\Bar\Baz\Qux as FooBarBazQux;
use VendorA\PackageA\Foo\Bar\Baz\Doom;
use VendorB\PackageB;
use VendorC\PackageC;
use const Bar\Baz\Qux as BarBazQux;
use const Doom\Bar\Baz\Qux as DoomBarBazQux;
use const Foo;
use const Foo\Bar\Baz\Qux as FooBarBazQux;
use const VendorA\PackageA\Foo\Bar\Baz\Doom;
use const VendorB\PackageB;
use const VendorC\PackageC;
use function Bar\Baz\Qux as BarBazQux;
use function Doom\Bar\Baz\Qux as DoomBarBazQux;
use function Foo;
use function Foo\Bar\Baz\Qux as FooBarBazQux;
use function VendorA\PackageA\Foo\Bar\Baz\Doom;
use function VendorB\PackageB;
use function VendorC\PackageC;

EOD;

        $this->assertSame($expected, $actual);
    }

    public function testGenerateContextDefaultNamespace()
    {
        $symbols = array(
            Symbol::fromString('\VendorC\PackageC'),
            Symbol::fromString('\VendorC\PackageC'),
            Symbol::fromString('\VendorB\PackageB'),
            Symbol::fromString('\VendorA\PackageA\Foo'),
            Symbol::fromString('\VendorA\PackageA\Foo\Bar\Baz'),
            Symbol::fromString('\VendorA\PackageA\Foo\Bar\Baz\Doom'),
            Symbol::fromString('\Foo\Bar\Baz\Qux'),
            Symbol::fromString('\Doom\Bar\Baz\Qux'),
            Symbol::fromString('\Bar\Baz\Qux'),
            Symbol::fromString('\Bar\Baz\Qux'),
            Symbol::fromString('\Foo'),
        );
        $context =
            $this->subject->generateContext(null, $symbols, array('function' => $symbols, 'const' => $symbols), 3);
        $actual = strval($context);
        $expected = <<<'EOD'
use Doom\Bar\Baz\Qux as DoomBarBazQux;
use Foo\Bar\Baz\Qux as FooBarBazQux;
use VendorA\PackageA\Foo\Bar\Baz;
use VendorA\PackageA\Foo\Bar\Baz\Doom;
use const Doom\Bar\Baz\Qux as DoomBarBazQux;
use const Foo\Bar\Baz\Qux as FooBarBazQux;
use const VendorA\PackageA\Foo\Bar\Baz;
use const VendorA\PackageA\Foo\Bar\Baz\Doom;
use function Doom\Bar\Baz\Qux as DoomBarBazQux;
use function Foo\Bar\Baz\Qux as FooBarBazQux;
use function VendorA\PackageA\Foo\Bar\Baz;
use function VendorA\PackageA\Foo\Bar\Baz\Doom;

EOD;

        $this->assertSame($expected, $actual);
    }

    public function testGenerateContextNoSymbols()
    {
        $this->assertSame('', strval($this->subject->generateContext()));
    }

    public function testInstance()
    {
        $class = get_class($this->subject);
        $liberatedClass = Liberator::liberateClass($class);
        $liberatedClass->instance = null;
        $actual = $class::instance();

        $this->assertInstanceOf($class, $actual);
        $this->assertSame($actual, $class::instance());
    }
}
