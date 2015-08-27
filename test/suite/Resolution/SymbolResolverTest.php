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

class SymbolResolverTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->functionResolver = function () {
            return true;
        };
        $this->constantResolver = function () {
            return true;
        };
        $this->symbolFactory = new SymbolFactory();
        $this->subject = new SymbolResolver($this->functionResolver, $this->constantResolver, $this->symbolFactory);

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
    }

    public function resolveData()
    {
        //                                               type        symbol               expected
        return array(
            'Qualified'                         => array(null,       '\VendorB\PackageB', '\VendorB\PackageB'),
            'Direct alias reference'            => array(null,       'PackageB',          '\VendorB\PackageB'),
            'Single atom reference'             => array(null,       'Symbol',            '\VendorA\PackageA\Symbol'),
            'Namespace atom'                    => array(null,       'namespace\Symbol',  '\VendorA\PackageA\Symbol'),

            'Qualified (function)'              => array('function', '\VendorD\PackageD', '\VendorD\PackageD'),
            'Direct alias reference (function)' => array('function', 'PackageD',          '\VendorD\PackageD'),
            'Single atom reference (function)'  => array('function', 'Symbol',            '\VendorA\PackageA\Symbol'),
            'Namespace atom (function)'         => array('function', 'namespace\Symbol',  '\VendorA\PackageA\Symbol'),

            'Qualified (constant)'              => array('const',    '\VendorF\PackageF', '\VendorF\PackageF'),
            'Direct alias reference (constant)' => array('const',    'PackageF',          '\VendorF\PackageF'),
            'Single atom reference (constant)'  => array('const',    'Symbol',            '\VendorA\PackageA\Symbol'),
            'Namespace atom (constant)'         => array('const',    'namespace\Symbol',  '\VendorA\PackageA\Symbol'),
        );
    }

    /**
     * @dataProvider resolveData
     */
    public function testResolve($type, $symbol, $expected)
    {
        $symbol = Symbol::fromString($symbol);

        $this->assertSame($expected, strval($this->subject->resolve($this->context, $symbol, $type)));
    }

    public function testResolveFunctionFallback()
    {
        $this->functionResolver = function () {
            return false;
        };
        $this->subject = new SymbolResolver($this->functionResolver, $this->constantResolver, $this->symbolFactory);
        $symbol = Symbol::fromString('Symbol');

        $this->assertSame('\Symbol', strval($this->subject->resolve($this->context, $symbol, 'function')));
    }

    public function testResolveConstantFallback()
    {
        $this->constantResolver = function () {
            return false;
        };
        $this->subject = new SymbolResolver($this->functionResolver, $this->constantResolver, $this->symbolFactory);
        $symbol = Symbol::fromString('Symbol');

        $this->assertSame('\Symbol', strval($this->subject->resolve($this->context, $symbol, 'const')));
    }

    public function testResolveFailureUnsupportedType()
    {
        $this->setExpectedException('InvalidArgumentException', "Unsupported symbol type 'invalid'.");
        $this->subject->resolve($this->context, Symbol::fromString('Symbol'), 'invalid');
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
