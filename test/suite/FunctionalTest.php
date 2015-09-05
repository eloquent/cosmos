<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos;

use Eloquent\Cosmos\Resolution\Context\ResolutionContextFactory;
use Eloquent\Cosmos\Resolution\SymbolResolver;
use Eloquent\Cosmos\Symbol\Symbol;
use Eloquent\Cosmos\Symbol\SymbolFactory;
use Eloquent\Cosmos\UseStatement\UseStatement;
use PHPUnit_Framework_TestCase;

class FunctionalTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->contextFactory = ResolutionContextFactory::instance();
        $this->symbolFactory = SymbolFactory::instance();
        $this->resolver = SymbolResolver::instance();
    }

    /**
     * Tests for PHP manual entry "Namespaces overview".
     *
     * Example "Example #1 Namespace syntax example"
     *
     * @link http://php.net/manual/en/language.namespaces.rationale.php#example-251
     */
    public function testResolveDocumentationOverviewExample1()
    {
        $context = $this->contextFactory->createContext(Symbol::fromString('\my\name'));

        $this->assertSame(
            '\my\name\MyClass',
            strval($this->resolver->resolve($context, Symbol::fromString('MyClass')))
        );
        $this->assertSame(
            '\my\name\MyClass',
            strval($this->resolver->resolve($context, Symbol::fromString('\my\name\MyClass')))
        );
    }

    /**
     * Tests for PHP manual entry "Using namespaces: Basics".
     *
     * Examples "file1.php" / "file2.php"
     *
     * @link http://php.net/manual/en/language.namespaces.basics.php
     */
    public function testResolveDocumentationBasicsExample0()
    {
        $functionResolver = function ($functionName) {
            return 'Foo\Bar\foo' === $functionName;
        };
        $constantResolver = function ($constantName) {
            return 'Foo\Bar\FOO' === $constantName;
        };
        $this->resolver = new SymbolResolver($this->symbolFactory, $functionResolver, $constantResolver);
        $context = $this->contextFactory->createContext(Symbol::fromString('\Foo\Bar'));

        $this->assertSame(
            '\Foo\Bar\foo',
            strval($this->resolver->resolve($context, Symbol::fromString('foo'), 'function'))
        );
        $this->assertSame(
            '\Foo\Bar\foo',
            strval($this->resolver->resolve($context, Symbol::fromString('foo')))
        );
        $this->assertSame(
            '\Foo\Bar\FOO',
            strval($this->resolver->resolve($context, Symbol::fromString('FOO'), 'const'))
        );
        $this->assertSame(
            '\Foo\Bar\subnamespace\foo',
            strval($this->resolver->resolve($context, Symbol::fromString('subnamespace\foo'), 'function'))
        );
        $this->assertSame(
            '\Foo\Bar\subnamespace\foo',
            strval($this->resolver->resolve($context, Symbol::fromString('subnamespace\foo')))
        );
        $this->assertSame(
            '\Foo\Bar\subnamespace\FOO',
            strval($this->resolver->resolve($context, Symbol::fromString('subnamespace\FOO'), 'const'))
        );
        $this->assertSame(
            '\Foo\Bar\foo',
            strval($this->resolver->resolve($context, Symbol::fromString('\Foo\Bar\foo'), 'function'))
        );
        $this->assertSame(
            '\Foo\Bar\foo',
            strval($this->resolver->resolve($context, Symbol::fromString('\Foo\Bar\foo')))
        );
        $this->assertSame(
            '\Foo\Bar\FOO',
            strval($this->resolver->resolve($context, Symbol::fromString('\Foo\Bar\FOO'), 'const'))
        );
    }

    // /**
    //  * Tests for PHP manual entry "Using namespaces: Basics".
    //  *
    //  * Example "Example #1 Accessing global classes, functions and constants from within a namespace"
    //  *
    //  * @link http://php.net/manual/en/language.namespaces.basics.php#example-259
    //  */
    // public function testResolveDocumentationBasicsExample1()
    // {
    //     $context = $this->contextFactory->createContext(Symbol::fromString('\Foo'));

    //     $this->assertSame(
    //         '\strlen',
    //         $this->resolver
    //             ->resolve($context, Symbol::fromString('\strlen'), 'function')
    //             ->string()
    //     );
    //     $this->assertSame(
    //         '\INI_ALL',
    //         $this->resolver
    //             ->resolve($context, Symbol::fromString('\INI_ALL'), 'const')
    //             ->string()
    //     );
    //     $this->assertSame(
    //         '\Exception',
    //         $this->resolver->resolve($context, Symbol::fromString('\Exception'))->string()
    //     );
    // }

    // /**
    //  * Tests for PHP manual entry "namespace keyword and __NAMESPACE__ constant".
    //  *
    //  * Example "Example #4 the namespace operator, inside a namespace"
    //  *
    //  * @link http://php.net/manual/en/language.namespaces.nsconstants.php#example-265
    //  */
    // public function testResolveDocumentationNamespaceKeywordExample4()
    // {
    //     $context = $this->contextFactory->createContext(
    //         Symbol::fromString('\MyProject'),
    //         array(
    //             UseStatement::fromSymbol(Symbol::fromString('\blah\blah'), Symbol::fromString('mine')),
    //         )
    //     );

    //     $this->assertSame(
    //         '\MyProject\blah\mine',
    //         $this->resolver
    //             ->resolve($context, Symbol::fromString('blah\mine'), 'function')
    //             ->string()
    //     );
    //     $this->assertSame(
    //         '\MyProject\blah\mine',
    //         $this->resolver
    //             ->resolve(
    //                 $context,
    //                 Symbol::fromString('namespace\blah\mine'),
    //                 'function'
    //             )
    //             ->string()
    //     );
    //     $this->assertSame(
    //         '\MyProject\func',
    //         $this->resolver
    //             ->resolve($context, Symbol::fromString('namespace\func'), 'function')
    //             ->string()
    //     );
    //     $this->assertSame(
    //         '\MyProject\sub\func',
    //         $this->resolver
    //             ->resolve(
    //                 $context,
    //                 Symbol::fromString('namespace\sub\func'),
    //                 'function'
    //             )
    //             ->string()
    //     );
    //     $this->assertSame(
    //         '\MyProject\cname',
    //         $this->resolver->resolve($context, Symbol::fromString('namespace\cname'))->string()
    //     );
    //     $this->assertSame(
    //         '\MyProject\sub\cname',
    //         $this->resolver->resolve($context, Symbol::fromString('namespace\sub\cname'))->string()
    //     );
    //     $this->assertSame(
    //         '\MyProject\CONSTANT',
    //         $this->resolver
    //             ->resolve(
    //                 $context,
    //                 Symbol::fromString('namespace\CONSTANT'),
    //                 'const'
    //             )
    //             ->string()
    //     );
    // }

    // /**
    //  * Tests for PHP manual entry "namespace keyword and __NAMESPACE__ constant".
    //  *
    //  * Example "Example #5 the namespace operator, in global code"
    //  *
    //  * @link http://php.net/manual/en/language.namespaces.nsconstants.php#example-266
    //  */
    // public function testResolveDocumentationNamespaceKeywordExample5()
    // {
    //     $context = $this->contextFactory->createContext();

    //     $this->assertSame(
    //         '\func',
    //         $this->resolver
    //             ->resolve($context, Symbol::fromString('namespace\func'), 'function')
    //             ->string()
    //     );
    //     $this->assertSame(
    //         '\sub\func',
    //         $this->resolver
    //             ->resolve(
    //                 $context,
    //                 Symbol::fromString('namespace\sub\func'),
    //                 'function'
    //             )
    //             ->string()
    //     );
    //     $this->assertSame(
    //         '\cname',
    //         $this->resolver->resolve($context, Symbol::fromString('namespace\cname'))->string()
    //     );
    //     $this->assertSame(
    //         '\sub\cname',
    //         $this->resolver->resolve($context, Symbol::fromString('namespace\sub\cname'))->string()
    //     );
    //     $this->assertSame(
    //         '\CONSTANT',
    //         $this->resolver
    //             ->resolve(
    //                 $context,
    //                 Symbol::fromString('namespace\CONSTANT'),
    //                 'const'
    //             )
    //             ->string()
    //     );
    // }

    // /**
    //  * Tests for PHP manual entry "Using namespaces: Aliasing/Importing".
    //  *
    //  * Example "Example #1 importing/aliasing with the use operator"
    //  *
    //  * @link http://php.net/manual/en/language.namespaces.importing.php#example-267
    //  */
    // public function testResolveDocumentationImportingExample1()
    // {
    //     $context = $this->contextFactory->createContext(
    //         Symbol::fromString('\foo'),
    //         array(
    //             UseStatement::fromSymbol(Symbol::fromString('\My\Full\Classname'), Symbol::fromString('Another')),
    //             UseStatement::fromSymbol(Symbol::fromString('\My\Full\NSname')),
    //             UseStatement::fromSymbol(Symbol::fromString('\ArrayObject')),
    //             UseStatement::fromSymbol(Symbol::fromString('\My\Full\functionName'), null, UseStatementType::FUNCT1ON()),
    //             UseStatement::fromSymbol(
    //                 Symbol::fromString('\My\Full\functionName'),
    //                 Symbol::fromString('func'),
    //                 UseStatementType::FUNCT1ON()
    //             ),
    //             UseStatement::fromSymbol(Symbol::fromString('\My\Full\CONSTANT'), null, UseStatementType::CONSTANT()),
    //         )
    //     );

    //     $this->assertSame(
    //         '\foo\Another',
    //         $this->resolver->resolve($context, Symbol::fromString('namespace\Another'))->string()
    //     );
    //     $this->assertSame(
    //         '\My\Full\Classname',
    //         $this->resolver->resolve($context, Symbol::fromString('Another'))->string()
    //     );
    //     $this->assertSame(
    //         '\My\Full\NSname\subns\func',
    //         $this->resolver
    //             ->resolve($context, Symbol::fromString('NSname\subns\func'), 'function')
    //             ->string()
    //     );
    //     $this->assertSame(
    //         '\ArrayObject',
    //         $this->resolver->resolve($context, Symbol::fromString('ArrayObject'))->string()
    //     );
    //     $this->assertSame(
    //         '\My\Full\functionName',
    //         $this->resolver->resolve($context, Symbol::fromString('func'), 'function')
    //             ->string()
    //     );
    //     $this->assertSame(
    //         '\My\Full\CONSTANT',
    //         $this->resolver
    //             ->resolve($context, Symbol::fromString('CONSTANT'), 'const')
    //             ->string()
    //     );
    // }

    // /**
    //  * Tests for PHP manual entry "Using namespaces: Aliasing/Importing".
    //  *
    //  * Example "Example #2 importing/aliasing with the use operator, multiple use statements combined"
    //  *
    //  * @link http://php.net/manual/en/language.namespaces.importing.php#example-268
    //  */
    // public function testResolveDocumentationImportingExample2()
    // {
    //     $context = $this->contextFactory->createContext(
    //         Symbol::fromString('\foo'),
    //         array(
    //             new UseStatement(
    //                 array(
    //                     new UseStatementClause(Symbol::fromString('\My\Full\Classname'), Symbol::fromString('Another')),
    //                     new UseStatementClause(Symbol::fromString('\My\Full\NSname')),
    //                 )
    //             ),
    //         )
    //     );

    //     $this->assertSame(
    //         '\My\Full\Classname',
    //         $this->resolver->resolve($context, Symbol::fromString('Another'))->string()
    //     );
    //     $this->assertSame(
    //         '\My\Full\NSname\subns\func',
    //         $this->resolver
    //             ->resolve($context, Symbol::fromString('NSname\subns\func'), 'function')
    //             ->string()
    //     );
    // }

    // /**
    //  * Tests for PHP manual entry "Using namespaces: Aliasing/Importing".
    //  *
    //  * Example "Example #4 Importing and fully qualified names"
    //  *
    //  * @link http://php.net/manual/en/language.namespaces.importing.php#example-270
    //  */
    // public function testResolveDocumentationImportingExample4()
    // {
    //     $context = $this->contextFactory->createContext(
    //         Symbol::fromString('\foo'),
    //         array(
    //             new UseStatement(
    //                 array(
    //                     new UseStatementClause(Symbol::fromString('\My\Full\Classname'), Symbol::fromString('Another')),
    //                     new UseStatementClause(Symbol::fromString('\My\Full\NSname')),
    //                 )
    //             ),
    //         )
    //     );

    //     $this->assertSame(
    //         '\My\Full\Classname',
    //         $this->resolver->resolve($context, Symbol::fromString('Another'))->string()
    //     );
    //     $this->assertSame(
    //         '\Another',
    //         $this->resolver->resolve($context, Symbol::fromString('\Another'))->string()
    //     );
    //     $this->assertSame(
    //         '\My\Full\Classname\thing',
    //         $this->resolver->resolve($context, Symbol::fromString('Another\thing'))->string()
    //     );
    //     $this->assertSame(
    //         '\Another\thing',
    //         $this->resolver->resolve($context, Symbol::fromString('\Another\thing'))->string()
    //     );
    // }

    // /**
    //  * Tests for PHP manual entry "Global space".
    //  *
    //  * Example "Example #1 Using global space specification"
    //  *
    //  * @link http://php.net/manual/en/language.namespaces.global.php#example-272
    //  */
    // public function testResolveDocumentationGlobalSpaceExample1()
    // {
    //     $context = $this->contextFactory->createContext(Symbol::fromString('\A\B\C'));

    //     $this->assertSame(
    //         '\fopen',
    //         $this->resolver
    //             ->resolve($context, Symbol::fromString('\fopen'), 'function')->string()
    //     );
    // }

    // /**
    //  * Tests for PHP manual entry "Using namespaces: fallback to global function/constant".
    //  *
    //  * Example "Example #1 Accessing global classes inside a namespace"
    //  *
    //  * @link http://php.net/manual/en/language.namespaces.fallback.php#example-273
    //  */
    // public function testResolveDocumentationFallbackExample1()
    // {
    //     $context = $this->contextFactory->createContext(Symbol::fromString('\A\B\C'));

    //     $this->assertSame(
    //         '\A\B\C\Exception',
    //         $this->resolver->resolve($context, Symbol::fromString('Exception'))->string()
    //     );
    //     $this->assertSame(
    //         '\Exception',
    //         $this->resolver->resolve($context, Symbol::fromString('\Exception'))->string()
    //     );
    //     $this->assertSame(
    //         '\A\B\C\ArrayObject',
    //         $this->resolver->resolve($context, Symbol::fromString('ArrayObject'))->string()
    //     );
    // }

    // /**
    //  * Tests for PHP manual entry "Using namespaces: fallback to global function/constant".
    //  *
    //  * Example "Example #2 global functions/constants fallback inside a namespace"
    //  *
    //  * @link http://php.net/manual/en/language.namespaces.fallback.php#example-274
    //  */
    // public function testResolveDocumentationFallbackExample2()
    // {
    //     $functionResolver = function ($functionName) {
    //         return '\A\B\C\strlen' === $functionName;
    //     };
    //     $constantResolver = function ($constantName) {
    //         return '\A\B\C\E_ERROR' === $constantName;
    //     };
    //     $this->resolver = new SymbolResolver($this->symbolFactory, $functionResolver, $constantResolver);
    //     $context = $this->contextFactory->createContext(Symbol::fromString('\A\B\C'));

    //     $this->assertSame(
    //         '\A\B\C\E_ERROR',
    //         $this->resolver
    //             ->resolve($context, Symbol::fromString('E_ERROR'), 'const')
    //             ->string()
    //     );
    //     $this->assertSame(
    //         '\INI_ALL',
    //         $this->resolver
    //             ->resolve($context, Symbol::fromString('INI_ALL'), 'const')
    //             ->string()
    //     );
    // }

    // /**
    //  * Tests for PHP manual entry "Name resolution rules".
    //  *
    //  * Example "Example #1 Name resolutions illustrated"
    //  *
    //  * @link http://php.net/manual/en/language.namespaces.rules.php#example-275
    //  */
    // public function testResolveDocumentationResolutionExample1()
    // {
    //     $context = $this->contextFactory->createContext(
    //         Symbol::fromString('\A'),
    //         array(
    //             new UseStatement(
    //                 array(
    //                     new UseStatementClause(Symbol::fromString('\B\D')),
    //                     new UseStatementClause(Symbol::fromString('\C\E'), Symbol::fromString('F')),
    //                 )
    //             ),
    //         )
    //     );

    //     // function calls
    //     $this->assertSame(
    //         '\A\foo',
    //         $this->resolver->resolve($context, Symbol::fromString('foo'), 'function')
    //             ->string()
    //     );
    //     $this->assertSame(
    //         '\foo',
    //         $this->resolver->resolve($context, Symbol::fromString('\foo'), 'function')
    //             ->string()
    //     );
    //     $this->assertSame(
    //         '\A\my\foo',
    //         $this->resolver->resolve($context, Symbol::fromString('my\foo'), 'function')
    //             ->string()
    //     );
    //     $this->assertSame(
    //         '\A\F',
    //         $this->resolver->resolve($context, Symbol::fromString('F'), 'function')
    //             ->string()
    //     );

    //     // class references
    //     $this->assertSame(
    //         '\A\B',
    //         $this->resolver->resolve($context, Symbol::fromString('B'))->string()
    //     );
    //     $this->assertSame(
    //         '\B\D',
    //         $this->resolver->resolve($context, Symbol::fromString('D'))->string()
    //     );
    //     $this->assertSame(
    //         '\C\E',
    //         $this->resolver->resolve($context, Symbol::fromString('F'))->string()
    //     );
    //     $this->assertSame(
    //         '\B',
    //         $this->resolver->resolve($context, Symbol::fromString('\B'))->string()
    //     );
    //     $this->assertSame(
    //         '\D',
    //         $this->resolver->resolve($context, Symbol::fromString('\D'))->string()
    //     );
    //     $this->assertSame(
    //         '\F',
    //         $this->resolver->resolve($context, Symbol::fromString('\F'))->string()
    //     );

    //     // static methods/namespace functions from another namespace
    //     $this->assertSame(
    //         '\A\B\foo',
    //         $this->resolver->resolve($context, Symbol::fromString('B\foo'), 'function')
    //             ->string()
    //     );
    //     $this->assertSame(
    //         '\A\B',
    //         $this->resolver->resolve($context, Symbol::fromString('B'))->string()
    //     );
    //     $this->assertSame(
    //         '\B\D',
    //         $this->resolver->resolve($context, Symbol::fromString('D'))->string()
    //     );
    //     $this->assertSame(
    //         '\B\foo',
    //         $this->resolver->resolve($context, Symbol::fromString('\B\foo'), 'function')
    //             ->string()
    //     );
    //     $this->assertSame(
    //         '\B',
    //         $this->resolver->resolve($context, Symbol::fromString('\B'))->string()
    //     );

    //     // static methods/namespace functions of current namespace
    //     $this->assertSame(
    //         '\A\A\B',
    //         $this->resolver->resolve($context, Symbol::fromString('A\B'))->string()
    //     );
    //     $this->assertSame(
    //         '\A\B',
    //         $this->resolver->resolve($context, Symbol::fromString('\A\B'))->string()
    //     );
    // }

    // /**
    //  * Tests for PHP manual entry "FAQ: things you need to know about namespaces".
    //  *
    //  * Example "If I don't use namespaces, should I care about any of this?"
    //  *
    //  * @link http://php.net/manual/en/language.namespaces.faq.php#language.namespaces.faq.shouldicare
    //  */
    // public function testResolveDocumentationFaqShouldICare()
    // {
    //     $context = $this->contextFactory->createContext();

    //     $this->assertSame(
    //         '\stdClass',
    //         $this->resolver->resolve($context, Symbol::fromString('\stdClass'))->string()
    //     );
    //     $this->assertSame(
    //         '\stdClass',
    //         $this->resolver->resolve($context, Symbol::fromString('stdClass'))->string()
    //     );
    // }

    // /**
    //  * Tests for PHP manual entry "FAQ: things you need to know about namespaces".
    //  *
    //  * Example "How do I use internal or global classes in a namespace?"
    //  *
    //  * @link http://php.net/manual/en/language.namespaces.faq.php#language.namespaces.faq.globalclass
    //  */
    // public function testResolveDocumentationFaqGlobalClass()
    // {
    //     $context = $this->contextFactory->createContext(Symbol::fromString('\foo'));

    //     $this->assertSame(
    //         '\stdClass',
    //         $this->resolver->resolve($context, Symbol::fromString('\stdClass'))->string()
    //     );
    //     $this->assertSame(
    //         '\ArrayObject',
    //         $this->resolver->resolve($context, Symbol::fromString('\ArrayObject'))->string()
    //     );
    //     $this->assertSame(
    //         '\DirectoryIterator',
    //         $this->resolver->resolve($context, Symbol::fromString('\DirectoryIterator'))->string()
    //     );
    //     $this->assertSame(
    //         '\Exception',
    //         $this->resolver->resolve($context, Symbol::fromString('\Exception'))->string()
    //     );
    // }

    // /**
    //  * Tests for PHP manual entry "FAQ: things you need to know about namespaces".
    //  *
    //  * Example "How do I use namespaces classes, functions, or constants in their own namespace?"
    //  *
    //  * @link http://php.net/manual/en/language.namespaces.faq.php#language.namespaces.faq.innamespace
    //  */
    // public function testResolveDocumentationFaqInNamespace()
    // {
    //     $context = $this->contextFactory->createContext(Symbol::fromString('\foo'));

    //     $this->assertSame(
    //         '\foo\MyClass',
    //         $this->resolver->resolve($context, Symbol::fromString('MyClass'))->string()
    //     );
    //     $this->assertSame(
    //         '\foo\MyClass',
    //         $this->resolver->resolve($context, Symbol::fromString('\foo\MyClass'))->string()
    //     );
    //     $this->assertSame(
    //         '\foo\MyClass',
    //         $this->resolver->resolve($context, Symbol::fromString('MyClass'))->string()
    //     );
    //     $this->assertSame(
    //         '\globalfunc',
    //         $this->resolver
    //             ->resolve($context, Symbol::fromString('\globalfunc'), 'function')
    //             ->string()
    //     );
    //     $this->assertSame(
    //         '\INI_ALL',
    //         $this->resolver
    //             ->resolve($context, Symbol::fromString('\INI_ALL'), 'const')
    //             ->string()
    //     );
    // }

    // /**
    //  * Tests for PHP manual entry "FAQ: things you need to know about namespaces".
    //  *
    //  * Example "How does a name like \my\name or \name resolve?"
    //  *
    //  * @link http://php.net/manual/en/language.namespaces.faq.php#language.namespaces.faq.full
    //  */
    // public function testResolveDocumentationFaqFull()
    // {
    //     $context = $this->contextFactory->createContext(Symbol::fromString('\foo'));

    //     $this->assertSame(
    //         '\my\name',
    //         $this->resolver->resolve($context, Symbol::fromString('\my\name'))->string()
    //     );
    //     $this->assertSame(
    //         '\strlen',
    //         $this->resolver
    //             ->resolve($context, Symbol::fromString('\strlen'), 'function')
    //             ->string()
    //     );
    //     $this->assertSame(
    //         '\INI_ALL',
    //         $this->resolver
    //             ->resolve($context, Symbol::fromString('\INI_ALL'), 'const')
    //             ->string()
    //     );
    // }

    // /**
    //  * Tests for PHP manual entry "FAQ: things you need to know about namespaces".
    //  *
    //  * Example "How does a name like my\name resolve?"
    //  *
    //  * @link http://php.net/manual/en/language.namespaces.faq.php#language.namespaces.faq.qualified
    //  */
    // public function testResolveDocumentationFaqQualified()
    // {
    //     $context = $this->contextFactory->createContext(
    //         Symbol::fromString('\foo'),
    //         array(
    //             UseStatement::fromSymbol(Symbol::fromString('\blah\blah'), Symbol::fromString('foo')),
    //         )
    //     );

    //     $this->assertSame(
    //         '\foo\my\name',
    //         $this->resolver->resolve($context, Symbol::fromString('my\name'))->string()
    //     );
    //     $this->assertSame(
    //         '\blah\blah\bar',
    //         $this->resolver->resolve($context, Symbol::fromString('foo\bar'))->string()
    //     );
    //     $this->assertSame(
    //         '\foo\my\bar',
    //         $this->resolver
    //             ->resolve($context, Symbol::fromString('my\bar'), 'function')
    //             ->string()
    //     );
    //     $this->assertSame(
    //         '\foo\my\BAR',
    //         $this->resolver
    //             ->resolve($context, Symbol::fromString('my\BAR'), 'const')
    //             ->string()
    //     );
    // }

    // /**
    //  * Tests for PHP manual entry "FAQ: things you need to know about namespaces".
    //  *
    //  * Example "How does an unqualified class name like name resolve?"
    //  *
    //  * @link http://php.net/manual/en/language.namespaces.faq.php#language.namespaces.faq.shortname1
    //  */
    // public function testResolveDocumentationFaqShortName1()
    // {
    //     $context = $this->contextFactory->createContext(
    //         Symbol::fromString('\foo'),
    //         array(
    //             UseStatement::fromSymbol(Symbol::fromString('\blah\blah'), Symbol::fromString('foo')),
    //         )
    //     );

    //     $this->assertSame(
    //         '\foo\name',
    //         $this->resolver->resolve($context, Symbol::fromString('name'))->string()
    //     );
    //     $this->assertSame(
    //         '\blah\blah',
    //         $this->resolver->resolve($context, Symbol::fromString('foo'))->string()
    //     );
    // }

    // /**
    //  * Tests for PHP manual entry "FAQ: things you need to know about namespaces".
    //  *
    //  * Example "How does an unqualified function name or unqualified constant name like name resolve?"
    //  *
    //  * @link http://php.net/manual/en/language.namespaces.faq.php#language.namespaces.faq.shortname2
    //  */
    // public function testResolveDocumentationFaqShortName2()
    // {
    //     $functionResolver = function ($functionName) {
    //         return in_array($functionName, array('\foo\my', '\foo\foo', '\foo\sort'), true);
    //     };
    //     $constantResolver = function ($constantName) {
    //         return '\foo\FOO' === $constantName;
    //     };
    //     $this->resolver = new SymbolResolver($this->symbolFactory, $functionResolver, $constantResolver);
    //     $context = $this->contextFactory->createContext(
    //         Symbol::fromString('\foo'),
    //         array(
    //             UseStatement::fromSymbol(Symbol::fromString('\blah\blah'), Symbol::fromString('foo')),
    //         )
    //     );

    //     $this->assertSame(
    //         '\sort',
    //         $this->resolver
    //             ->resolve($context, Symbol::fromString('\sort'), 'function')->string()
    //     );
    //     $this->assertSame(
    //         '\foo\my',
    //         $this->resolver
    //             ->resolve($context, Symbol::fromString('my'), 'function')->string()
    //     );
    //     $this->assertSame(
    //         '\strlen',
    //         $this->resolver
    //             ->resolve($context, Symbol::fromString('strlen'), 'function')->string()
    //     );
    //     $this->assertSame(
    //         '\foo\sort',
    //         $this->resolver
    //             ->resolve($context, Symbol::fromString('sort'), 'function')->string()
    //     );
    //     $this->assertSame(
    //         '\foo\foo',
    //         $this->resolver
    //             ->resolve($context, Symbol::fromString('foo'), 'function')->string()
    //     );
    //     $this->assertSame(
    //         '\foo\FOO',
    //         $this->resolver
    //             ->resolve($context, Symbol::fromString('FOO'), 'const')->string()
    //     );
    //     $this->assertSame(
    //         '\INI_ALL',
    //         $this->resolver
    //             ->resolve($context, Symbol::fromString('INI_ALL'), 'const')->string()
    //     );
    // }

    // /**
    //  * Tests for PHP manual entry "FAQ: things you need to know about namespaces".
    //  *
    //  * Example "Neither functions nor constants can be imported via the use statement."
    //  *
    //  * @link http://php.net/manual/en/language.namespaces.faq.php#language.namespaces.faq.nofuncconstantuse
    //  */
    // public function testResolveDocumentationFaqNoFunctionConstantImport()
    // {
    //     $context = $this->contextFactory->createContext(
    //         Symbol::fromString('\mine'),
    //         array(
    //             UseStatement::fromSymbol(Symbol::fromString('\ultra\long\ns\name')),
    //         )
    //     );

    //     $this->assertSame(
    //         '\ultra\long\ns\name\CONSTANT',
    //         $this->resolver
    //             ->resolve($context, Symbol::fromString('name\CONSTANT'), 'const')
    //             ->string()
    //     );
    //     $this->assertSame(
    //         '\ultra\long\ns\name\func',
    //         $this->resolver
    //             ->resolve($context, Symbol::fromString('name\func'), 'function')
    //             ->string()
    //     );
    // }

    // /**
    //  * Tests for PHP manual entry "FAQ: things you need to know about namespaces".
    //  *
    //  * Example "Undefined Constants referenced using any backslash die with fatal error"
    //  *
    //  * @link http://php.net/manual/en/language.namespaces.faq.php#language.namespaces.faq.constants
    //  */
    // public function testResolveDocumentationFaqConstants()
    // {
    //     $functionResolver = function ($functionName) {
    //         return false;
    //     };
    //     $constantResolver = function ($constantName) {
    //         return false;
    //     };
    //     $this->resolver = new SymbolResolver($this->symbolFactory, $functionResolver, $constantResolver);
    //     $context = $this->contextFactory->createContext(Symbol::fromString('\bar'));

    //     $this->assertSame(
    //         '\FOO',
    //         $this->resolver
    //             ->resolve($context, Symbol::fromString('FOO'), 'const')->string()
    //     );
    //     $this->assertSame(
    //         '\FOO',
    //         $this->resolver
    //             ->resolve($context, Symbol::fromString('\FOO'), 'const')->string()
    //     );
    //     $this->assertSame(
    //         '\bar\Bar\FOO',
    //         $this->resolver
    //             ->resolve($context, Symbol::fromString('Bar\FOO'), 'const')->string()
    //     );
    //     $this->assertSame(
    //         '\Bar\FOO',
    //         $this->resolver
    //             ->resolve($context, Symbol::fromString('\Bar\FOO'), 'const')
    //             ->string()
    //     );
    // }

    // /**
    //  * Tests for PHP manual entry "Migrating from PHP 5.5.x to PHP 5.6.x: New features".
    //  *
    //  * Example "use function and use const"
    //  *
    //  * @link http://php.net//manual/en/migration56.new-features.php#migration56.new-features.use
    //  */
    // public function testResolveDocumentationNewIn56UseFunctionConst()
    // {
    //     $context = $this->contextFactory->createContext(
    //         null,
    //         array(
    //             UseStatement::fromSymbol(Symbol::fromString('\Name\Space\FOO'), null, UseStatementType::CONSTANT()),
    //             UseStatement::fromSymbol(Symbol::fromString('\Name\Space\f'), null, UseStatementType::FUNCT1ON()),
    //         )
    //     );

    //     $this->assertSame(
    //         '\Name\Space\FOO',
    //         $this->resolver
    //             ->resolve($context, Symbol::fromString('FOO'), 'const')->string()
    //     );
    //     $this->assertSame(
    //         '\Name\Space\f',
    //         $this->resolver
    //             ->resolve($context, Symbol::fromString('f'), 'function')->string()
    //     );
    // }
}
