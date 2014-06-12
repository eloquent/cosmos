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

use Eloquent\Cosmos\Resolution\Context\ResolutionContext;
use Eloquent\Cosmos\Symbol\Factory\SymbolFactory;
use Eloquent\Cosmos\UseStatement\UseStatement;
use Eloquent\Liberator\Liberator;
use PHPUnit_Framework_TestCase;

class SymbolResolverTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->resolver = new SymbolResolver;

        $this->symbolFactory = new SymbolFactory;

        $this->primaryNamespace = $this->symbolFactory->create('\VendorA\PackageA');
        $this->useStatements = array(
            new UseStatement($this->symbolFactory->create('\VendorB\PackageB')),
            new UseStatement($this->symbolFactory->create('\VendorC\PackageC')),
        );
        $this->context = new ResolutionContext($this->primaryNamespace, $this->useStatements, $this->symbolFactory);
    }

    public function testResolve()
    {
        $qualified = $this->symbolFactory->create('\VendorB\PackageB');
        $reference = $this->symbolFactory->create('Symbol');

        $this->assertSame($qualified, $this->resolver->resolve($this->primaryNamespace, $qualified));
        $this->assertSame(
            '\VendorA\PackageA\Symbol',
            $this->resolver->resolve($this->primaryNamespace, $reference)->string()
        );
    }

    public function testResolveAgainstContext()
    {
        $qualified = $this->symbolFactory->create('\VendorB\PackageB');
        $reference = $this->symbolFactory->create('Symbol');

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
            $this->resolver->resolveAgainstContext($this->context, $this->symbolFactory->create('Symbol'))->string()
        );
        $this->assertSame(
            '\Vendor\Package',
            $this->resolver->resolveAgainstContext($this->context, $this->symbolFactory->create('Vendor\Package'))
                ->string()
        );
    }

    /**
     * @link http://php.net/manual/en/language.namespaces.importing.php
     */
    public function testResolveAgainstContextDocumentationExamples()
    {
        $this->context = new ResolutionContext(
            $this->symbolFactory->create('\foo'),
            array(
                new UseStatement(
                    $this->symbolFactory->create('\My\Full\Classname'),
                    $this->symbolFactory->create('Another')
                ),
                new UseStatement($this->symbolFactory->create('\My\Full\NSname')),
                new UseStatement($this->symbolFactory->create('\ArrayObject')),
            )
        );

        $this->assertSame(
            '\foo\Another',
            $this->resolver->resolveAgainstContext($this->context, $this->symbolFactory->create('namespace\Another'))
                ->string()
        );
        $this->assertSame(
            '\My\Full\Classname',
            $this->resolver->resolveAgainstContext($this->context, $this->symbolFactory->create('Another'))->string()
        );
        $this->assertSame(
            '\My\Full\Classname\thing',
            $this->resolver->resolveAgainstContext($this->context, $this->symbolFactory->create('Another\thing'))
                ->string()
        );
        $this->assertSame(
            '\My\Full\NSname\subns',
            $this->resolver->resolveAgainstContext($this->context, $this->symbolFactory->create('NSname\subns'))
                ->string()
        );
        $this->assertSame(
            '\ArrayObject',
            $this->resolver->resolveAgainstContext($this->context, $this->symbolFactory->create('ArrayObject'))
                ->string()
        );
    }

    public function testResolveAgainstContextSpecialAtoms()
    {
        $this->assertSame(
            '\VendorA\PackageA\.\PackageB\Symbol',
            $this->resolver->resolveAgainstContext($this->context, $this->symbolFactory->create('.\PackageB\Symbol'))
                ->string()
        );
        $this->assertSame(
            '\VendorA\PackageA\..\PackageD\Symbol',
            $this->resolver->resolveAgainstContext($this->context, $this->symbolFactory->create('..\PackageD\Symbol'))
                ->string()
        );
        $this->assertSame(
            '\VendorB\PackageB\..\PackageD\Symbol',
            $this->resolver
                ->resolveAgainstContext($this->context, $this->symbolFactory->create('PackageB\..\PackageD\Symbol'))
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
        $this->primaryNamespace = $this->symbolFactory->create('\Foo\Bar');
        $this->useStatements = array(
            new UseStatement($this->symbolFactory->create('\Baz\Qux')),
            new UseStatement($this->symbolFactory->create('\Doom\Splat'), $this->symbolFactory->create('Ping')),
            new UseStatement($this->symbolFactory->create('\Pong\Pang')),
            new UseStatement($this->symbolFactory->create('\Pong\Pang\Peng')),
        );
        $this->context = new ResolutionContext($this->primaryNamespace, $this->useStatements, $this->symbolFactory);

        $this->assertSame(
            $expected,
            $this->resolver->relativeToContext($this->context, $this->symbolFactory->create($symbolString))->string()
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
