<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution;

use Eloquent\Cosmos\Resolution\Context\ResolutionContext;
use Eloquent\Cosmos\Symbol\Symbol;
use Eloquent\Cosmos\Symbol\SymbolFactory;
use Eloquent\Cosmos\UseStatement\UseStatement;
use Eloquent\Liberator\Liberator;
use PHPUnit_Framework_TestCase;

/**
 * @covers \Eloquent\Cosmos\Resolution\ConstantSymbolResolver
 */
class ConstantSymbolResolverTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->symbolFactory = new SymbolFactory();
        $this->constantResolver = function () {
            return true;
        };
        $this->subject = new ConstantSymbolResolver($this->symbolFactory, $this->constantResolver);

        $this->primaryNamespace = Symbol::fromString('\VendorA\PackageA');
        $this->useStatements = array(
            UseStatement::fromSymbol(Symbol::fromString('\VendorB\PackageB')),
            UseStatement::fromSymbol(Symbol::fromString('\VendorC\PackageC')),
            UseStatement::fromSymbol(Symbol::fromString('\VendorD\PackageD'), null, 'function'),
            UseStatement::fromSymbol(Symbol::fromString('\VendorE\PackageE'), null, 'function'),
            UseStatement::fromSymbol(Symbol::fromString('\VendorF\PackageF'), null, 'const'),
            UseStatement::fromSymbol(Symbol::fromString('\VendorG\PackageG'), null, 'const'),
        );
        $this->context = new ResolutionContext($this->primaryNamespace, $this->useStatements);
        $this->globalContext = new ResolutionContext(null, $this->useStatements);
    }

    public function resolveData()
    {
        //                                              symbol               expected
        return array(
            'Qualified'                        => array('\VendorF\PackageF', '\VendorF\PackageF'),
            'Direct alias reference'           => array('PackageF',          '\VendorF\PackageF'),
            'Direct alias reference plus atom' => array('PackageB\Symbol',   '\VendorB\PackageB\Symbol'),
            'Single atom reference'            => array('Symbol',            '\VendorA\PackageA\Symbol'),
            'Multiple atom reference'          => array('NamespaceA\Symbol', '\VendorA\PackageA\NamespaceA\Symbol'),
            'Namespace atom'                   => array('namespace\Symbol',  '\VendorA\PackageA\Symbol'),
        );
    }

    /**
     * @dataProvider resolveData
     */
    public function testResolve($symbol, $expected)
    {
        $this->assertSame($expected, strval($this->subject->resolve($this->context, Symbol::fromString($symbol))));
    }

    public function resolveGlobalData()
    {
        //                                              symbol               expected
        return array(
            'Qualified'                        => array('\VendorF\PackageF', '\VendorF\PackageF'),
            'Direct alias reference'           => array('PackageF',          '\VendorF\PackageF'),
            'Direct alias reference plus atom' => array('PackageB\Symbol',   '\VendorB\PackageB\Symbol'),
            'Single atom reference'            => array('Symbol',            '\Symbol'),
            'Multiple atom reference'          => array('NamespaceA\Symbol', '\NamespaceA\Symbol'),
            'Namespace atom'                   => array('namespace\Symbol',  '\Symbol'),
        );
    }

    /**
     * @dataProvider resolveGlobalData
     */
    public function testResolveGlobal($symbol, $expected)
    {
        $this->assertSame(
            $expected,
            strval($this->subject->resolve($this->globalContext, Symbol::fromString($symbol)))
        );
    }

    public function testResolveGlobalFallback()
    {
        $this->constantResolver = function () {
            return false;
        };
        $this->subject = new ConstantSymbolResolver($this->symbolFactory, $this->constantResolver);
        $symbol = Symbol::fromString('Symbol');

        $this->assertSame('\Symbol', strval($this->subject->resolve($this->context, $symbol)));
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
