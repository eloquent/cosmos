<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution;

use Eloquent\Cosmos\Resolution\Context\Factory\ResolutionContextFactory;
use Eloquent\Cosmos\Resolution\Context\ResolutionContext;
use Eloquent\Cosmos\Symbol\Symbol;
use Eloquent\Cosmos\Symbol\SymbolType;
use Eloquent\Cosmos\UseStatement\UseStatement;
use Eloquent\Cosmos\UseStatement\UseStatementType;
use Eloquent\Liberator\Liberator;
use PHPUnit_Framework_TestCase;

class SymbolResolverTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->functionResolver = function ($functionName) {
            return function_exists($functionName);
        };
        $this->constantResolver = function ($constantName) {
            return defined($constantName);
        };
        $this->contextFactory = new ResolutionContextFactory;
        $this->resolver = new SymbolResolver($this->functionResolver, $this->constantResolver, $this->contextFactory);

        $this->primaryNamespace = Symbol::fromString('\VendorA\PackageA');
        $this->useStatements = array(
            UseStatement::create(Symbol::fromString('\VendorB\PackageB')),
            UseStatement::create(Symbol::fromString('\VendorC\PackageC')),
            UseStatement::create(Symbol::fromString('\VendorD\PackageD'), null, UseStatementType::FUNCT1ON()),
            UseStatement::create(Symbol::fromString('\VendorE\PackageE'), null, UseStatementType::FUNCT1ON()),
            UseStatement::create(Symbol::fromString('\VendorF\PackageF'), null, UseStatementType::CONSTANT()),
            UseStatement::create(Symbol::fromString('\VendorG\PackageG'), null, UseStatementType::CONSTANT()),
        );
        $this->context = new ResolutionContext($this->primaryNamespace, $this->useStatements);
    }

    public function testConstructor()
    {
        $this->assertSame($this->functionResolver, $this->resolver->functionResolver());
        $this->assertSame($this->constantResolver, $this->resolver->constantResolver());
        $this->assertSame($this->contextFactory, $this->resolver->contextFactory());
    }

    public function testConstructorDefaults()
    {
        $this->resolver = new SymbolResolver;

        $this->assertSame('function_exists', $this->resolver->functionResolver());
        $this->assertSame('defined', $this->resolver->constantResolver());
        $this->assertSame(ResolutionContextFactory::instance(), $this->resolver->contextFactory());
    }

    public function testResolve()
    {
        $qualified = Symbol::fromString('\VendorB\PackageB');
        $reference = Symbol::fromString('Symbol');

        $this->assertSame($qualified, $this->resolver->resolve($this->primaryNamespace, $qualified));
        $this->assertSame(
            '\VendorA\PackageA\Symbol',
            $this->resolver->resolve($this->primaryNamespace, $reference)->string()
        );
    }

    public function testResolveNamespaceAtom()
    {
        $qualified = Symbol::fromString('\VendorB\PackageB');
        $reference = Symbol::fromString('namespace\Symbol');

        $this->assertSame($qualified, $this->resolver->resolve($this->primaryNamespace, $qualified));
        $this->assertSame(
            '\VendorA\PackageA\Symbol',
            $this->resolver->resolve($this->primaryNamespace, $reference)->string()
        );
    }

    public function testResolveEmpty()
    {
        $qualified = Symbol::fromString('\VendorB\PackageB');
        $reference = Symbol::fromString('');

        $this->assertSame($qualified, $this->resolver->resolve($this->primaryNamespace, $qualified));
        $this->assertSame(
            '\VendorA\PackageA\.',
            $this->resolver->resolve($this->primaryNamespace, $reference)->string()
        );
    }

    public function testResolveAgainstContext()
    {
        $qualified = Symbol::fromString('\VendorB\PackageB');
        $reference = Symbol::fromString('Symbol');

        $this->assertSame($qualified, $this->resolver->resolveAgainstContext($this->context, $qualified));
        $this->assertSame(
            '\VendorA\PackageA\Symbol',
            $this->resolver->resolveAgainstContext($this->context, $reference)->string()
        );
    }

    public function testResolveAgainstContextGlobalNsNoUseStatements()
    {
        $this->context = new ResolutionContext;

        $this->assertSame(
            '\Symbol',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('Symbol'))->string()
        );
        $this->assertSame(
            '\Vendor\Package',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('Vendor\Package'))
                ->string()
        );
    }

    /**
     * @link http://php.net/manual/en/language.namespaces.importing.php
     */
    public function testResolveAgainstContextDocumentationExamples()
    {
        $this->context = new ResolutionContext(
            Symbol::fromString('\foo'),
            array(
                UseStatement::create(Symbol::fromString('\My\Full\Classname'), Symbol::fromString('Another')),
                UseStatement::create(Symbol::fromString('\My\Full\NSname')),
                UseStatement::create(Symbol::fromString('\ArrayObject')),
                UseStatement::create(Symbol::fromString('\My\Full\functionName'), null, UseStatementType::FUNCT1ON()),
                UseStatement::create(
                    Symbol::fromString('\My\Full\functionName'),
                    Symbol::fromString('func'),
                    UseStatementType::FUNCT1ON()
                ),
                UseStatement::create(Symbol::fromString('\My\Full\CONSTANT'), null, UseStatementType::CONSTANT()),
            )
        );

        $this->assertSame(
            '\foo\Another',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('namespace\Another'))->string()
        );
        $this->assertSame(
            '\My\Full\Classname',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('Another'))->string()
        );
        $this->assertSame(
            '\My\Full\NSname\subns\func',
            $this->resolver
                ->resolveAgainstContext($this->context, Symbol::fromString('NSname\subns\func'), SymbolType::FUNCT1ON())
                ->string()
        );
        $this->assertSame(
            '\ArrayObject',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('ArrayObject'))->string()
        );
        $this->assertSame(
            '\My\Full\functionName',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('func'), SymbolType::FUNCT1ON())
                ->string()
        );
        $this->assertSame(
            '\My\Full\CONSTANT',
            $this->resolver
                ->resolveAgainstContext($this->context, Symbol::fromString('CONSTANT'), SymbolType::CONSTANT())
                ->string()
        );
        $this->assertSame(
            '\Another',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('\Another'))->string()
        );
        $this->assertSame(
            '\My\Full\Classname\thing',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('Another\thing'))->string()
        );
        $this->assertSame(
            '\Another\thing',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('\Another\thing'))->string()
        );
    }

    public function testResolveAgainstContextSpecialAtoms()
    {
        $this->assertSame(
            '\VendorA\PackageA\.\PackageB\Symbol',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('.\PackageB\Symbol'))->string()
        );
        $this->assertSame(
            '\VendorA\PackageA\..\PackageD\Symbol',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('..\PackageD\Symbol'))->string()
        );
        $this->assertSame(
            '\VendorB\PackageB\..\PackageD\Symbol',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('PackageB\..\PackageD\Symbol'))
                ->string()
        );
    }

    public function relativeToContextData()
    {
        //                                           symbol                      expected
        return array(
            'Primary namespace +1'          => array('\Foo\Bar\Baz',             'Baz'),
            'Primary namespace +2'          => array('\Foo\Bar\Baz\Qux',         'Baz\Qux'),
            'Primary namespace +3'          => array('\Foo\Bar\Baz\Qux\Doom',    'Baz\Qux\Doom'),
            'Use statement'                 => array('\Baz\Qux',                 'Qux'),
            'Use statement +1'              => array('\Baz\Qux\Doom',            'Qux\Doom'),
            'Use statement +2'              => array('\Baz\Qux\Doom\Splat',      'Qux\Doom\Splat'),
            'Alias'                         => array('\Doom\Splat',              'Ping'),
            'Alias +1'                      => array('\Doom\Splat\Pong',         'Ping\Pong'),
            'Alias +2'                      => array('\Doom\Splat\Pong\Pang',    'Ping\Pong\Pang'),
            'Shortest use statement'        => array('\Pong\Pang\Peng',          'Peng'),
            'Use statement not too short'   => array('\Pong\Pang\Ping',          'Pang\Ping'),
            'No relevant statements'        => array('\Zing\Zang\Zong',          '\Zing\Zang\Zong'),
            'Avoid use statement clash'     => array('\Foo\Bar\Qux',             'namespace\Qux'),
            'Avoid use statement clash + N' => array('\Foo\Bar\Qux\Doom\Splat',  'namespace\Qux\Doom\Splat'),
            'Avoid use alias clash'         => array('\Foo\Bar\Ping',            'namespace\Ping'),
            'Avoid use alias clash + N'     => array('\Foo\Bar\Ping\Doom\Splat', 'namespace\Ping\Doom\Splat'),
        );
    }

    /**
     * @dataProvider relativeToContextData
     */
    public function testRelativeToContext($symbolString, $expected)
    {
        $this->primaryNamespace = Symbol::fromString('\Foo\Bar');
        $this->useStatements = array(
            UseStatement::create(Symbol::fromString('\Baz\Qux')),
            UseStatement::create(Symbol::fromString('\Doom\Splat'), Symbol::fromString('Ping')),
            UseStatement::create(Symbol::fromString('\Pong\Pang')),
            UseStatement::create(Symbol::fromString('\Pong\Pang\Peng')),
        );
        $this->context = new ResolutionContext($this->primaryNamespace, $this->useStatements);

        $this->assertSame(
            $expected,
            $this->resolver->relativeToContext($this->context, Symbol::fromString($symbolString))->string()
        );
    }

    public function testInstance()
    {
        $class = get_class($this->resolver);
        $liberatedClass = Liberator::liberateClass($class);
        $liberatedClass->instance = null;
        $actual = $class::instance();

        $this->assertInstanceOf($class, $actual);
        $this->assertSame($actual, $class::instance());
    }
}
