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
use Eloquent\Cosmos\UseStatement\UseStatementClause;
use Eloquent\Cosmos\UseStatement\UseStatementType;
use Eloquent\Liberator\Liberator;
use PHPUnit_Framework_TestCase;

class SymbolResolverTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->functionResolver = function ($functionName) {
            return true;
        };
        $this->constantResolver = function ($constantName) {
            return true;
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

    public function resolveData()
    {
        //                                              symbol               type        expected
        return array(
            'Qualified'                        => array('\VendorB\PackageB', 'class',    '\VendorB\PackageB'),
            'Single atom reference'            => array('Symbol',            'class',    '\VendorA\PackageA\Symbol'),
            'Namespace atom'                   => array('namespace\Symbol',  'class',    '\VendorA\PackageA\Symbol'),
            'Self atom'                        => array('.',                 'class',    '\VendorA\PackageA\.'),

            'Qualified (function)'             => array('\VendorD\PackageD', 'function', '\VendorD\PackageD'),
            'Single atom reference (function)' => array('Symbol',            'function', '\VendorA\PackageA\Symbol'),
            'Namespace atom (function)'        => array('namespace\Symbol',  'function', '\VendorA\PackageA\Symbol'),
            'Self atom (function)'             => array('.',                 'function', '\VendorA\PackageA\.'),

            'Qualified (constant)'             => array('\VendorF\PackageF', 'const',    '\VendorF\PackageF'),
            'Single atom reference (constant)' => array('Symbol',            'const',    '\VendorA\PackageA\Symbol'),
            'Namespace atom (constant)'        => array('namespace\Symbol',  'const',    '\VendorA\PackageA\Symbol'),
            'Self atom (constant)'             => array('.',                 'const',    '\VendorA\PackageA\.'),
        );
    }

    /**
     * @dataProvider resolveData
     */
    public function testResolve($symbol, $type, $expected)
    {
        $symbol = Symbol::fromString($symbol);
        $type = SymbolType::memberByValue($type);

        $this->assertSame($expected, $this->resolver->resolveAsType($this->primaryNamespace, $symbol, $type)->string());
        if ($type->isType()) {
            $this->assertSame($expected, $this->resolver->resolve($this->primaryNamespace, $symbol)->string());
        }
    }

    public function resolverAgainstContextData()
    {
        //                                              symbol                         type        expected
        return array(
            'Qualified'                        => array('\VendorB\PackageB',           'class',    '\VendorB\PackageB'),
            'Single atom reference'            => array('Symbol',                      'class',    '\VendorA\PackageA\Symbol'),
            'Namespace atom'                   => array('namespace\Symbol',            'class',    '\VendorA\PackageA\Symbol'),
            'Self atom'                        => array('.',                           'class',    '\VendorA\PackageA\.'),
            'Self atom + others'               => array('.\PackageB\Symbol',           'class',    '\VendorA\PackageA\.\PackageB\Symbol'),
            'Parent atom + others'             => array('..\PackageD\Symbol',          'class',    '\VendorA\PackageA\..\PackageD\Symbol'),
            'Parent atom mid-symbol'           => array('PackageB\..\PackageD\Symbol', 'class',    '\VendorB\PackageB\..\PackageD\Symbol'),

            'Qualified (function)'             => array('\VendorD\PackageD',           'function', '\VendorD\PackageD'),
            'Single atom reference (function)' => array('Symbol',                      'function', '\VendorA\PackageA\Symbol'),
            'Namespace atom (function)'        => array('namespace\Symbol',            'function', '\VendorA\PackageA\Symbol'),
            'Self atom (function)'             => array('.',                           'function', '\VendorA\PackageA\.'),

            'Qualified (constant)'             => array('\VendorF\PackageF',           'const',    '\VendorF\PackageF'),
            'Single atom reference (constant)' => array('Symbol',                      'const',    '\VendorA\PackageA\Symbol'),
            'Namespace atom (constant)'        => array('namespace\Symbol',            'const',    '\VendorA\PackageA\Symbol'),
            'Self atom (constant)'             => array('.',                           'const',    '\VendorA\PackageA\.'),
        );
    }

    /**
     * @dataProvider resolverAgainstContextData
     */
    public function testResolveAgainstContext($symbol, $type, $expected)
    {
        $symbol = Symbol::fromString($symbol);
        $type = SymbolType::memberByValue($type);

        $this->assertSame($expected, $this->resolver->resolveAgainstContext($this->context, $symbol, $type)->string());
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
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('Vendor\Package'))->string()
        );
    }

    public function testResolveSingleAtomFunctionAgainstGlobal()
    {
        $symbol = Symbol::fromString('Symbol');
        $context = new ResolutionContext;

        $this->assertSame(
            '\Symbol',
            $this->resolver->resolveAgainstContext($context, $symbol, SymbolType::FUNCT1ON())->string()
        );
    }

    /**
     * Tests for PHP manual entry "Namespaces overview"
     *
     * Example "Example #1 Namespace syntax example"
     *
     * @link http://php.net/manual/en/language.namespaces.rationale.php#example-251
     */
    public function testResolveAgainstContextDocumentationOverviewExample1()
    {
        $this->context = new ResolutionContext(Symbol::fromString('\my\name'));

        $this->assertSame(
            '\my\name\MyClass',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('MyClass'))->string()
        );
        $this->assertSame(
            '\my\name\MyClass',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('\my\name\MyClass'))->string()
        );
    }

    /**
     * Tests for PHP manual entry "Using namespaces: Basics"
     *
     * Examples "file1.php" / "file2.php"
     *
     * @link http://php.net/manual/en/language.namespaces.basics.php
     */
    public function testResolveAgainstContextDocumentationBasicsExample0()
    {
        $this->functionResolver = function ($functionName) {
            return '\Foo\Bar\foo' === $functionName;
        };
        $this->constantResolver = function ($constantName) {
            return '\Foo\Bar\FOO' === $constantName;
        };
        $this->resolver = new SymbolResolver($this->functionResolver, $this->constantResolver, $this->contextFactory);
        $this->context = new ResolutionContext(Symbol::fromString('\Foo\Bar'));

        $this->assertSame(
            '\Foo\Bar\foo',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('foo'), SymbolType::FUNCT1ON())
                ->string()
        );
        $this->assertSame(
            '\Foo\Bar\foo',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('foo'))->string()
        );
        $this->assertSame(
            '\Foo\Bar\FOO',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('FOO'), SymbolType::CONSTANT())
                ->string()
        );
        $this->assertSame(
            '\Foo\Bar\subnamespace\foo',
            $this->resolver
                ->resolveAgainstContext($this->context, Symbol::fromString('subnamespace\foo'), SymbolType::FUNCT1ON())
                ->string()
        );
        $this->assertSame(
            '\Foo\Bar\subnamespace\foo',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('subnamespace\foo'))->string()
        );
        $this->assertSame(
            '\Foo\Bar\subnamespace\FOO',
            $this->resolver
                ->resolveAgainstContext($this->context, Symbol::fromString('subnamespace\FOO'), SymbolType::CONSTANT())
                ->string()
        );
        $this->assertSame(
            '\Foo\Bar\foo',
            $this->resolver
                ->resolveAgainstContext($this->context, Symbol::fromString('\Foo\Bar\foo'), SymbolType::FUNCT1ON())
                ->string()
        );
        $this->assertSame(
            '\Foo\Bar\foo',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('\Foo\Bar\foo'))->string()
        );
        $this->assertSame(
            '\Foo\Bar\FOO',
            $this->resolver
                ->resolveAgainstContext($this->context, Symbol::fromString('\Foo\Bar\FOO'), SymbolType::CONSTANT())
                ->string()
        );
    }

    /**
     * Tests for PHP manual entry "Using namespaces: Basics"
     *
     * Example "Example #1 Accessing global classes, functions and constants from within a namespace"
     *
     * @link http://php.net/manual/en/language.namespaces.basics.php#example-259
     */
    public function testResolveAgainstContextDocumentationBasicsExample1()
    {
        $this->context = new ResolutionContext(Symbol::fromString('\Foo'));

        $this->assertSame(
            '\strlen',
            $this->resolver
                ->resolveAgainstContext($this->context, Symbol::fromString('\strlen'), SymbolType::FUNCT1ON())
                ->string()
        );
        $this->assertSame(
            '\INI_ALL',
            $this->resolver
                ->resolveAgainstContext($this->context, Symbol::fromString('\INI_ALL'), SymbolType::CONSTANT())
                ->string()
        );
        $this->assertSame(
            '\Exception',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('\Exception'))->string()
        );
    }

    /**
     * Tests for PHP manual entry "namespace keyword and __NAMESPACE__ constant"
     *
     * Example "Example #4 the namespace operator, inside a namespace"
     *
     * @link http://php.net/manual/en/language.namespaces.nsconstants.php#example-265
     */
    public function testResolveAgainstContextDocumentationNamespaceKeywordExample4()
    {
        $this->context = new ResolutionContext(
            Symbol::fromString('\MyProject'),
            array(
                UseStatement::create(Symbol::fromString('\blah\blah'), Symbol::fromString('mine')),
            )
        );

        $this->assertSame(
            '\MyProject\blah\mine',
            $this->resolver
                ->resolveAgainstContext($this->context, Symbol::fromString('blah\mine'), SymbolType::FUNCT1ON())
                ->string()
        );
        $this->assertSame(
            '\MyProject\blah\mine',
            $this->resolver
                ->resolveAgainstContext(
                    $this->context,
                    Symbol::fromString('namespace\blah\mine'),
                    SymbolType::FUNCT1ON()
                )
                ->string()
        );
        $this->assertSame(
            '\MyProject\func',
            $this->resolver
                ->resolveAgainstContext($this->context, Symbol::fromString('namespace\func'), SymbolType::FUNCT1ON())
                ->string()
        );
        $this->assertSame(
            '\MyProject\sub\func',
            $this->resolver
                ->resolveAgainstContext(
                    $this->context,
                    Symbol::fromString('namespace\sub\func'),
                    SymbolType::FUNCT1ON()
                )
                ->string()
        );
        $this->assertSame(
            '\MyProject\cname',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('namespace\cname'))->string()
        );
        $this->assertSame(
            '\MyProject\sub\cname',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('namespace\sub\cname'))->string()
        );
        $this->assertSame(
            '\MyProject\CONSTANT',
            $this->resolver
                ->resolveAgainstContext(
                    $this->context,
                    Symbol::fromString('namespace\CONSTANT'),
                    SymbolType::CONSTANT()
                )
                ->string()
        );
    }

    /**
     * Tests for PHP manual entry "namespace keyword and __NAMESPACE__ constant"
     *
     * Example "Example #5 the namespace operator, in global code"
     *
     * @link http://php.net/manual/en/language.namespaces.nsconstants.php#example-266
     */
    public function testResolveAgainstContextDocumentationNamespaceKeywordExample5()
    {
        $this->context = new ResolutionContext;

        $this->assertSame(
            '\func',
            $this->resolver
                ->resolveAgainstContext($this->context, Symbol::fromString('namespace\func'), SymbolType::FUNCT1ON())
                ->string()
        );
        $this->assertSame(
            '\sub\func',
            $this->resolver
                ->resolveAgainstContext(
                    $this->context,
                    Symbol::fromString('namespace\sub\func'),
                    SymbolType::FUNCT1ON()
                )
                ->string()
        );
        $this->assertSame(
            '\cname',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('namespace\cname'))->string()
        );
        $this->assertSame(
            '\sub\cname',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('namespace\sub\cname'))->string()
        );
        $this->assertSame(
            '\CONSTANT',
            $this->resolver
                ->resolveAgainstContext(
                    $this->context,
                    Symbol::fromString('namespace\CONSTANT'),
                    SymbolType::CONSTANT()
                )
                ->string()
        );
    }

    /**
     * Tests for PHP manual entry "Using namespaces: Aliasing/Importing"
     *
     * Example "Example #1 importing/aliasing with the use operator"
     *
     * @link http://php.net/manual/en/language.namespaces.importing.php#example-267
     */
    public function testResolveAgainstContextDocumentationImportingExample1()
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
    }

    /**
     * Tests for PHP manual entry "Using namespaces: Aliasing/Importing"
     *
     * Example "Example #2 importing/aliasing with the use operator, multiple use statements combined"
     *
     * @link http://php.net/manual/en/language.namespaces.importing.php#example-268
     */
    public function testResolveAgainstContextDocumentationImportingExample2()
    {
        $this->context = new ResolutionContext(
            Symbol::fromString('\foo'),
            array(
                new UseStatement(
                    array(
                        new UseStatementClause(Symbol::fromString('\My\Full\Classname'), Symbol::fromString('Another')),
                        new UseStatementClause(Symbol::fromString('\My\Full\NSname')),
                    )
                ),
            )
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
    }

    /**
     * Tests for PHP manual entry "Using namespaces: Aliasing/Importing"
     *
     * Example "Example #4 Importing and fully qualified names"
     *
     * @link http://php.net/manual/en/language.namespaces.importing.php#example-270
     */
    public function testResolveAgainstContextDocumentationImportingExample4()
    {
        $this->context = new ResolutionContext(
            Symbol::fromString('\foo'),
            array(
                new UseStatement(
                    array(
                        new UseStatementClause(Symbol::fromString('\My\Full\Classname'), Symbol::fromString('Another')),
                        new UseStatementClause(Symbol::fromString('\My\Full\NSname')),
                    )
                ),
            )
        );

        $this->assertSame(
            '\My\Full\Classname',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('Another'))->string()
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

    /**
     * Tests for PHP manual entry "Global space"
     *
     * Example "Example #1 Using global space specification"
     *
     * @link http://php.net/manual/en/language.namespaces.global.php#example-272
     */
    public function testResolveAgainstContextDocumentationGlobalSpaceExample1()
    {
        $this->context = new ResolutionContext(Symbol::fromString('\A\B\C'));

        $this->assertSame(
            '\fopen',
            $this->resolver
                ->resolveAgainstContext($this->context, Symbol::fromString('\fopen'), SymbolType::FUNCT1ON())->string()
        );
    }

    /**
     * Tests for PHP manual entry "Using namespaces: fallback to global function/constant"
     *
     * Example "Example #1 Accessing global classes inside a namespace"
     *
     * @link http://php.net/manual/en/language.namespaces.fallback.php#example-273
     */
    public function testResolveAgainstContextDocumentationFallbackExample1()
    {
        $this->context = new ResolutionContext(Symbol::fromString('\A\B\C'));

        $this->assertSame(
            '\A\B\C\Exception',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('Exception'))->string()
        );
        $this->assertSame(
            '\Exception',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('\Exception'))->string()
        );
        $this->assertSame(
            '\A\B\C\ArrayObject',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('ArrayObject'))->string()
        );
    }

    /**
     * Tests for PHP manual entry "Using namespaces: fallback to global function/constant"
     *
     * Example "Example #2 global functions/constants fallback inside a namespace"
     *
     * @link http://php.net/manual/en/language.namespaces.fallback.php#example-274
     */
    public function testResolveAgainstContextDocumentationFallbackExample2()
    {
        $this->functionResolver = function ($functionName) {
            return '\A\B\C\strlen' === $functionName;
        };
        $this->constantResolver = function ($constantName) {
            return '\A\B\C\E_ERROR' === $constantName;
        };
        $this->resolver = new SymbolResolver($this->functionResolver, $this->constantResolver, $this->contextFactory);
        $this->context = new ResolutionContext(Symbol::fromString('\A\B\C'));

        $this->assertSame(
            '\A\B\C\E_ERROR',
            $this->resolver
                ->resolveAgainstContext($this->context, Symbol::fromString('E_ERROR'), SymbolType::CONSTANT())
                ->string()
        );
        $this->assertSame(
            '\INI_ALL',
            $this->resolver
                ->resolveAgainstContext($this->context, Symbol::fromString('INI_ALL'), SymbolType::CONSTANT())
                ->string()
        );
    }

    /**
     * Tests for PHP manual entry "Name resolution rules"
     *
     * Example "Example #1 Name resolutions illustrated"
     *
     * @link http://php.net/manual/en/language.namespaces.rules.php#example-275
     */
    public function testResolveAgainstContextDocumentationResolutionExample1()
    {
        $this->context = new ResolutionContext(
            Symbol::fromString('\A'),
            array(
                new UseStatement(
                    array(
                        new UseStatementClause(Symbol::fromString('\B\D')),
                        new UseStatementClause(Symbol::fromString('\C\E'), Symbol::fromString('F')),
                    )
                ),
            )
        );

        // function calls
        $this->assertSame(
            '\A\foo',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('foo'), SymbolType::FUNCT1ON())
                ->string()
        );
        $this->assertSame(
            '\foo',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('\foo'), SymbolType::FUNCT1ON())
                ->string()
        );
        $this->assertSame(
            '\A\my\foo',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('my\foo'), SymbolType::FUNCT1ON())
                ->string()
        );
        $this->assertSame(
            '\A\F',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('F'), SymbolType::FUNCT1ON())
                ->string()
        );

        // class references
        $this->assertSame(
            '\A\B',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('B'))->string()
        );
        $this->assertSame(
            '\B\D',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('D'))->string()
        );
        $this->assertSame(
            '\C\E',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('F'))->string()
        );
        $this->assertSame(
            '\B',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('\B'))->string()
        );
        $this->assertSame(
            '\D',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('\D'))->string()
        );
        $this->assertSame(
            '\F',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('\F'))->string()
        );

        // static methods/namespace functions from another namespace
        $this->assertSame(
            '\A\B\foo',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('B\foo'), SymbolType::FUNCT1ON())
                ->string()
        );
        $this->assertSame(
            '\A\B',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('B'))->string()
        );
        $this->assertSame(
            '\B\D',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('D'))->string()
        );
        $this->assertSame(
            '\B\foo',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('\B\foo'), SymbolType::FUNCT1ON())
                ->string()
        );
        $this->assertSame(
            '\B',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('\B'))->string()
        );

        // static methods/namespace functions of current namespace
        $this->assertSame(
            '\A\A\B',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('A\B'))->string()
        );
        $this->assertSame(
            '\A\B',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('\A\B'))->string()
        );
    }

    /**
     * Tests for PHP manual entry "FAQ: things you need to know about namespaces"
     *
     * Example "If I don't use namespaces, should I care about any of this?"
     *
     * @link http://php.net/manual/en/language.namespaces.faq.php#language.namespaces.faq.shouldicare
     */
    public function testResolveAgainstContextDocumentationFaqShouldICare()
    {
        $this->context = new ResolutionContext;

        $this->assertSame(
            '\stdClass',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('\stdClass'))->string()
        );
        $this->assertSame(
            '\stdClass',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('stdClass'))->string()
        );
    }

    /**
     * Tests for PHP manual entry "FAQ: things you need to know about namespaces"
     *
     * Example "How do I use internal or global classes in a namespace?"
     *
     * @link http://php.net/manual/en/language.namespaces.faq.php#language.namespaces.faq.globalclass
     */
    public function testResolveAgainstContextDocumentationFaqGlobalClass()
    {
        $this->context = new ResolutionContext(Symbol::fromString('\foo'));

        $this->assertSame(
            '\stdClass',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('\stdClass'))->string()
        );
        $this->assertSame(
            '\ArrayObject',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('\ArrayObject'))->string()
        );
        $this->assertSame(
            '\DirectoryIterator',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('\DirectoryIterator'))->string()
        );
        $this->assertSame(
            '\Exception',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('\Exception'))->string()
        );
    }

    /**
     * Tests for PHP manual entry "FAQ: things you need to know about namespaces"
     *
     * Example "How do I use namespaces classes, functions, or constants in their own namespace?"
     *
     * @link http://php.net/manual/en/language.namespaces.faq.php#language.namespaces.faq.innamespace
     */
    public function testResolveAgainstContextDocumentationFaqInNamespace()
    {
        $this->context = new ResolutionContext(Symbol::fromString('\foo'));

        $this->assertSame(
            '\foo\MyClass',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('MyClass'))->string()
        );
        $this->assertSame(
            '\foo\MyClass',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('\foo\MyClass'))->string()
        );
        $this->assertSame(
            '\foo\MyClass',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('MyClass'))->string()
        );
        $this->assertSame(
            '\globalfunc',
            $this->resolver
                ->resolveAgainstContext($this->context, Symbol::fromString('\globalfunc'), SymbolType::FUNCT1ON())
                ->string()
        );
        $this->assertSame(
            '\INI_ALL',
            $this->resolver
                ->resolveAgainstContext($this->context, Symbol::fromString('\INI_ALL'), SymbolType::CONSTANT())
                ->string()
        );
    }

    /**
     * Tests for PHP manual entry "FAQ: things you need to know about namespaces"
     *
     * Example "How does a name like \my\name or \name resolve?"
     *
     * @link http://php.net/manual/en/language.namespaces.faq.php#language.namespaces.faq.full
     */
    public function testResolveAgainstContextDocumentationFaqFull()
    {
        $this->context = new ResolutionContext(Symbol::fromString('\foo'));

        $this->assertSame(
            '\my\name',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('\my\name'))->string()
        );
        $this->assertSame(
            '\strlen',
            $this->resolver
                ->resolveAgainstContext($this->context, Symbol::fromString('\strlen'), SymbolType::FUNCT1ON())
                ->string()
        );
        $this->assertSame(
            '\INI_ALL',
            $this->resolver
                ->resolveAgainstContext($this->context, Symbol::fromString('\INI_ALL'), SymbolType::CONSTANT())
                ->string()
        );
    }

    /**
     * Tests for PHP manual entry "FAQ: things you need to know about namespaces"
     *
     * Example "How does a name like my\name resolve?"
     *
     * @link http://php.net/manual/en/language.namespaces.faq.php#language.namespaces.faq.qualified
     */
    public function testResolveAgainstContextDocumentationFaqQualified()
    {
        $this->context = new ResolutionContext(
            Symbol::fromString('\foo'),
            array(
                UseStatement::create(Symbol::fromString('\blah\blah'), Symbol::fromString('foo')),
            )
        );

        $this->assertSame(
            '\foo\my\name',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('my\name'))->string()
        );
        $this->assertSame(
            '\blah\blah\bar',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('foo\bar'))->string()
        );
        $this->assertSame(
            '\foo\my\bar',
            $this->resolver
                ->resolveAgainstContext($this->context, Symbol::fromString('my\bar'), SymbolType::FUNCT1ON())
                ->string()
        );
        $this->assertSame(
            '\foo\my\BAR',
            $this->resolver
                ->resolveAgainstContext($this->context, Symbol::fromString('my\BAR'), SymbolType::CONSTANT())
                ->string()
        );
    }

    /**
     * Tests for PHP manual entry "FAQ: things you need to know about namespaces"
     *
     * Example "How does an unqualified class name like name resolve?"
     *
     * @link http://php.net/manual/en/language.namespaces.faq.php#language.namespaces.faq.shortname1
     */
    public function testResolveAgainstContextDocumentationFaqShortName1()
    {
        $this->context = new ResolutionContext(
            Symbol::fromString('\foo'),
            array(
                UseStatement::create(Symbol::fromString('\blah\blah'), Symbol::fromString('foo')),
            )
        );

        $this->assertSame(
            '\foo\name',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('name'))->string()
        );
        $this->assertSame(
            '\blah\blah',
            $this->resolver->resolveAgainstContext($this->context, Symbol::fromString('foo'))->string()
        );
    }

    /**
     * Tests for PHP manual entry "FAQ: things you need to know about namespaces"
     *
     * Example "How does an unqualified function name or unqualified constant name like name resolve?"
     *
     * @link http://php.net/manual/en/language.namespaces.faq.php#language.namespaces.faq.shortname2
     */
    public function testResolveAgainstContextDocumentationFaqShortName2()
    {
        $this->functionResolver = function ($functionName) {
            return in_array($functionName, array('\foo\my', '\foo\foo', '\foo\sort'), true);
        };
        $this->constantResolver = function ($constantName) {
            return '\foo\FOO' === $constantName;
        };
        $this->resolver = new SymbolResolver($this->functionResolver, $this->constantResolver, $this->contextFactory);
        $this->context = new ResolutionContext(
            Symbol::fromString('\foo'),
            array(
                UseStatement::create(Symbol::fromString('\blah\blah'), Symbol::fromString('foo')),
            )
        );

        $this->assertSame(
            '\sort',
            $this->resolver
                ->resolveAgainstContext($this->context, Symbol::fromString('\sort'), SymbolType::FUNCT1ON())->string()
        );
        $this->assertSame(
            '\foo\my',
            $this->resolver
                ->resolveAgainstContext($this->context, Symbol::fromString('my'), SymbolType::FUNCT1ON())->string()
        );
        $this->assertSame(
            '\strlen',
            $this->resolver
                ->resolveAgainstContext($this->context, Symbol::fromString('strlen'), SymbolType::FUNCT1ON())->string()
        );
        $this->assertSame(
            '\foo\sort',
            $this->resolver
                ->resolveAgainstContext($this->context, Symbol::fromString('sort'), SymbolType::FUNCT1ON())->string()
        );
        $this->assertSame(
            '\foo\foo',
            $this->resolver
                ->resolveAgainstContext($this->context, Symbol::fromString('foo'), SymbolType::FUNCT1ON())->string()
        );
        $this->assertSame(
            '\foo\FOO',
            $this->resolver
                ->resolveAgainstContext($this->context, Symbol::fromString('FOO'), SymbolType::CONSTANT())->string()
        );
        $this->assertSame(
            '\INI_ALL',
            $this->resolver
                ->resolveAgainstContext($this->context, Symbol::fromString('INI_ALL'), SymbolType::CONSTANT())->string()
        );
    }

    /**
     * Tests for PHP manual entry "FAQ: things you need to know about namespaces"
     *
     * Example "Neither functions nor constants can be imported via the use statement."
     *
     * @link http://php.net/manual/en/language.namespaces.faq.php#language.namespaces.faq.nofuncconstantuse
     */
    public function testResolveAgainstContextDocumentationFaqNoFunctionConstantImport()
    {
        $this->context = new ResolutionContext(
            Symbol::fromString('\mine'),
            array(
                UseStatement::create(Symbol::fromString('\ultra\long\ns\name')),
            )
        );

        $this->assertSame(
            '\ultra\long\ns\name\CONSTANT',
            $this->resolver
                ->resolveAgainstContext($this->context, Symbol::fromString('name\CONSTANT'), SymbolType::CONSTANT())
                ->string()
        );
        $this->assertSame(
            '\ultra\long\ns\name\func',
            $this->resolver
                ->resolveAgainstContext($this->context, Symbol::fromString('name\func'), SymbolType::FUNCT1ON())
                ->string()
        );
    }

    /**
     * Tests for PHP manual entry "FAQ: things you need to know about namespaces"
     *
     * Example "Undefined Constants referenced using any backslash die with fatal error"
     *
     * @link http://php.net/manual/en/language.namespaces.faq.php#language.namespaces.faq.constants
     */
    public function testResolveAgainstContextDocumentationFaqConstants()
    {
        $this->functionResolver = function ($functionName) {
            return false;
        };
        $this->constantResolver = function ($constantName) {
            return false;
        };
        $this->resolver = new SymbolResolver($this->functionResolver, $this->constantResolver, $this->contextFactory);
        $this->context = new ResolutionContext(Symbol::fromString('\bar'));

        $this->assertSame(
            '\FOO',
            $this->resolver
                ->resolveAgainstContext($this->context, Symbol::fromString('FOO'), SymbolType::CONSTANT())->string()
        );
        $this->assertSame(
            '\FOO',
            $this->resolver
                ->resolveAgainstContext($this->context, Symbol::fromString('\FOO'), SymbolType::CONSTANT())->string()
        );
        $this->assertSame(
            '\bar\Bar\FOO',
            $this->resolver
                ->resolveAgainstContext($this->context, Symbol::fromString('Bar\FOO'), SymbolType::CONSTANT())->string()
        );
        $this->assertSame(
            '\Bar\FOO',
            $this->resolver
                ->resolveAgainstContext($this->context, Symbol::fromString('\Bar\FOO'), SymbolType::CONSTANT())
                ->string()
        );
    }

    /**
     * Tests for PHP manual entry "Migrating from PHP 5.5.x to PHP 5.6.x: New features"
     *
     * Example "use function and use const"
     *
     * @link http://php.net//manual/en/migration56.new-features.php#migration56.new-features.use
     */
    public function testResolveAgainstContextDocumentationNewIn56UseFunctionConst()
    {
        $this->context = new ResolutionContext(
            null,
            array(
                UseStatement::create(Symbol::fromString('\Name\Space\FOO'), null, UseStatementType::CONSTANT()),
                UseStatement::create(Symbol::fromString('\Name\Space\f'), null, UseStatementType::FUNCT1ON()),
            )
        );

        $this->assertSame(
            '\Name\Space\FOO',
            $this->resolver
                ->resolveAgainstContext($this->context, Symbol::fromString('FOO'), SymbolType::CONSTANT())->string()
        );
        $this->assertSame(
            '\Name\Space\f',
            $this->resolver
                ->resolveAgainstContext($this->context, Symbol::fromString('f'), SymbolType::FUNCT1ON())->string()
        );
    }

    public function relativeToContextData()
    {
        //                                                      symbol                                      type        expected
        return array(
            'Primary namespace +1'                     => array('\SymbolA\SymbolB\SymbolC',                 'class',    'SymbolC'),
            'Primary namespace +2'                     => array('\SymbolA\SymbolB\SymbolC\SymbolD',         'class',    'SymbolC\SymbolD'),
            'Primary namespace +3'                     => array('\SymbolA\SymbolB\SymbolC\SymbolD\SymbolE', 'class',    'SymbolC\SymbolD\SymbolE'),
            'Use statement'                            => array('\SymbolC\SymbolD',                         'class',    'SymbolD'),
            'Use statement +1'                         => array('\SymbolC\SymbolD\SymbolE',                 'class',    'SymbolD\SymbolE'),
            'Use statement +2'                         => array('\SymbolC\SymbolD\SymbolE\SymbolF',         'class',    'SymbolD\SymbolE\SymbolF'),
            'Alias'                                    => array('\SymbolE\SymbolF',                         'class',    'SymbolG'),
            'Alias +1'                                 => array('\SymbolE\SymbolF\SymbolH',                 'class',    'SymbolG\SymbolH'),
            'Alias +2'                                 => array('\SymbolE\SymbolF\SymbolH\SymbolI',         'class',    'SymbolG\SymbolH\SymbolI'),
            'Shortest use statement'                   => array('\SymbolH\SymbolI\SymbolJ',                 'class',    'SymbolJ'),
            'Use statement not too short'              => array('\SymbolH\SymbolI\SymbolG',                 'class',    'SymbolI\SymbolG'),
            'No relevant statements'                   => array('\Foo\Bar\Baz',                             'class',    '\Foo\Bar\Baz'),
            'Avoid use statement clash'                => array('\SymbolA\SymbolB\SymbolD',                 'class',    'namespace\SymbolD'),
            'Avoid use statement clash + N'            => array('\SymbolA\SymbolB\SymbolD\SymbolE\SymbolF', 'class',    'namespace\SymbolD\SymbolE\SymbolF'),
            'Avoid use alias clash'                    => array('\SymbolA\SymbolB\SymbolG',                 'class',    'namespace\SymbolG'),
            'Avoid use alias clash + N'                => array('\SymbolA\SymbolB\SymbolG\SymbolE\SymbolF', 'class',    'namespace\SymbolG\SymbolE\SymbolF'),

            'Primary namespace +1 (function)'          => array('\SymbolA\SymbolB\SymbolM',                 'function', 'SymbolM'),
            'Primary namespace +2 (function)'          => array('\SymbolA\SymbolB\SymbolM\SymbolN',         'function', 'SymbolM\SymbolN'),
            'Primary namespace +3 (function)'          => array('\SymbolA\SymbolB\SymbolM\SymbolN\SymbolO', 'function', 'SymbolM\SymbolN\SymbolO'),
            'Use statement (function)'                 => array('\SymbolM\SymbolN',                         'function', 'SymbolN'),
            'Use statement +1 (function)'              => array('\SymbolM\SymbolN\SymbolO',                 'function', 'SymbolN\SymbolO'),
            'Use statement +2 (function)'              => array('\SymbolM\SymbolN\SymbolO\SymbolP',         'function', 'SymbolN\SymbolO\SymbolP'),
            'Alias (function)'                         => array('\SymbolO\SymbolP',                         'function', 'SymbolQ'),
            'Alias +1 (function)'                      => array('\SymbolO\SymbolP\SymbolR',                 'function', 'SymbolQ\SymbolR'),
            'Alias +2 (function)'                      => array('\SymbolO\SymbolP\SymbolR\SymbolS',         'function', 'SymbolQ\SymbolR\SymbolS'),
            'Shortest use statement (function)'        => array('\SymbolR\SymbolS\SymbolT',                 'function', 'SymbolT'),
            'Use statement not too short (function)'   => array('\SymbolR\SymbolS\SymbolQ',                 'function', 'SymbolS\SymbolQ'),
            'No relevant statements (function)'        => array('\Foo\Bar\Baz',                             'function', '\Foo\Bar\Baz'),
            'Avoid use statement clash (function)'     => array('\SymbolA\SymbolB\SymbolN',                 'function', 'namespace\SymbolN'),
            'Avoid use statement clash + N (function)' => array('\SymbolA\SymbolB\SymbolN\SymbolO\SymbolP', 'function', 'namespace\SymbolN\SymbolO\SymbolP'),
            'Avoid use alias clash (function)'         => array('\SymbolA\SymbolB\SymbolQ',                 'function', 'namespace\SymbolQ'),
            'Avoid use alias clash + N (function)'     => array('\SymbolA\SymbolB\SymbolQ\SymbolO\SymbolP', 'function', 'namespace\SymbolQ\SymbolO\SymbolP'),

            'Primary namespace +1 (constant)'          => array('\SymbolA\SymbolB\SymbolU',                 'const',    'SymbolU'),
            'Primary namespace +2 (constant)'          => array('\SymbolA\SymbolB\SymbolU\SymbolV',         'const',    'SymbolU\SymbolV'),
            'Primary namespace +3 (constant)'          => array('\SymbolA\SymbolB\SymbolU\SymbolV\SymbolW', 'const',    'SymbolU\SymbolV\SymbolW'),
            'Use statement (constant)'                 => array('\SymbolU\SymbolV',                         'const',    'SymbolV'),
            'Use statement +1 (constant)'              => array('\SymbolU\SymbolV\SymbolW',                 'const',    'SymbolV\SymbolW'),
            'Use statement +2 (constant)'              => array('\SymbolU\SymbolV\SymbolW\SymbolX',         'const',    'SymbolV\SymbolW\SymbolX'),
            'Alias (constant)'                         => array('\SymbolW\SymbolX',                         'const',    'SymbolY'),
            'Alias +1 (constant)'                      => array('\SymbolW\SymbolX\SymbolZ',                 'const',    'SymbolY\SymbolZ'),
            'Alias +2 (constant)'                      => array('\SymbolW\SymbolX\SymbolZ\SymbolAA',        'const',    'SymbolY\SymbolZ\SymbolAA'),
            'Shortest use statement (constant)'        => array('\SymbolZ\SymbolAA\SymbolAB',               'const',    'SymbolAB'),
            'Use statement not too short (constant)'   => array('\SymbolZ\SymbolAA\SymbolY',                'const',    'SymbolAA\SymbolY'),
            'No relevant statements (constant)'        => array('\Foo\Bar\Baz',                             'const',    '\Foo\Bar\Baz'),
            'Avoid use statement clash (constant)'     => array('\SymbolA\SymbolB\SymbolV',                 'const',    'namespace\SymbolV'),
            'Avoid use statement clash + N (constant)' => array('\SymbolA\SymbolB\SymbolV\SymbolW\SymbolX', 'const',    'namespace\SymbolV\SymbolW\SymbolX'),
            'Avoid use alias clash (constant)'         => array('\SymbolA\SymbolB\SymbolY',                 'const',    'namespace\SymbolY'),
            'Avoid use alias clash + N (constant)'     => array('\SymbolA\SymbolB\SymbolY\SymbolW\SymbolX', 'const',    'namespace\SymbolY\SymbolW\SymbolX'),
        );
    }

    /**
     * @dataProvider relativeToContextData
     */
    public function testRelativeToContext($symbolString, $type, $expected)
    {
        $this->primaryNamespace = Symbol::fromString('\SymbolA\SymbolB');
        $this->useStatements = array(
            UseStatement::create(Symbol::fromString('\SymbolC\SymbolD')),
            UseStatement::create(Symbol::fromString('\SymbolE\SymbolF'), Symbol::fromString('SymbolG')),
            UseStatement::create(Symbol::fromString('\SymbolH\SymbolI')),
            UseStatement::create(Symbol::fromString('\SymbolH\SymbolI\SymbolJ')),
            UseStatement::create(Symbol::fromString('\SymbolM\SymbolN'), null, UseStatementType::FUNCT1ON()),
            UseStatement::create(
                Symbol::fromString('\SymbolO\SymbolP'),
                Symbol::fromString('SymbolQ'),
                UseStatementType::FUNCT1ON()
            ),
            UseStatement::create(Symbol::fromString('\SymbolR\SymbolS'), null, UseStatementType::FUNCT1ON()),
            UseStatement::create(Symbol::fromString('\SymbolR\SymbolS\SymbolT'), null, UseStatementType::FUNCT1ON()),
            UseStatement::create(Symbol::fromString('\SymbolU\SymbolV'), null, UseStatementType::CONSTANT()),
            UseStatement::create(
                Symbol::fromString('\SymbolW\SymbolX'),
                Symbol::fromString('SymbolY'),
                UseStatementType::CONSTANT()
            ),
            UseStatement::create(Symbol::fromString('\SymbolZ\SymbolAA'), null, UseStatementType::CONSTANT()),
            UseStatement::create(Symbol::fromString('\SymbolZ\SymbolAA\SymbolAB'), null, UseStatementType::CONSTANT()),
        );
        $this->context = new ResolutionContext($this->primaryNamespace, $this->useStatements);

        $this->assertSame(
            $expected,
            $this->resolver
                ->relativeToContext($this->context, Symbol::fromString($symbolString), SymbolType::memberByValue($type))
                ->string()
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
