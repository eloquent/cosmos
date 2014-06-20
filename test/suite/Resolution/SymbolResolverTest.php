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
