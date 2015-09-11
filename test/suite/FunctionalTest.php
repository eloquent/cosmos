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

use Eloquent\Cosmos\Persistence\ResolutionContextReader;
use Eloquent\Cosmos\Resolution\ConstantSymbolResolver;
use Eloquent\Cosmos\Resolution\FunctionSymbolResolver;
use Eloquent\Cosmos\Resolution\SymbolResolver;
use Eloquent\Cosmos\Symbol\Symbol;
use Eloquent\Cosmos\Symbol\SymbolFactory;
use PHPUnit_Framework_TestCase;

/**
 * @coversNothing
 */
class FunctionalTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->symbolFactory = SymbolFactory::instance();
        $this->resolver = SymbolResolver::instance();
        $this->functionResolver = FunctionSymbolResolver::instance();
        $this->constantResolver = ConstantSymbolResolver::instance();

        $this->contextReader = ResolutionContextReader::instance();
    }

    /**
     * Tests for PHP manual entry "Namespaces overview".
     *
     * Example "Example #1 Namespace syntax example"
     *
     * @see http://php.net/manual/en/language.namespaces.rationale.php
     */
    public function testResolveDocumentationOverviewExample1()
    {
        $context = $this->contextFromSource('namespace my\name;');

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
     * @see http://php.net/manual/en/language.namespaces.basics.php
     */
    public function testResolveDocumentationBasicsExample0()
    {
        $functionResolver = function ($functionName) {
            return 'Foo\Bar\foo' === $functionName;
        };
        $constantResolver = function ($constantName) {
            return 'Foo\Bar\FOO' === $constantName;
        };
        $this->functionResolver = new FunctionSymbolResolver($this->symbolFactory, $functionResolver);
        $this->constantResolver = new ConstantSymbolResolver($this->symbolFactory, $constantResolver);
        $context = $this->contextFromSource('namespace Foo\Bar;');

        $this->assertSame(
            '\Foo\Bar\foo',
            strval($this->functionResolver->resolve($context, Symbol::fromString('foo')))
        );
        $this->assertSame(
            '\Foo\Bar\foo',
            strval($this->resolver->resolve($context, Symbol::fromString('foo')))
        );
        $this->assertSame(
            '\Foo\Bar\FOO',
            strval($this->constantResolver->resolve($context, Symbol::fromString('FOO')))
        );
        $this->assertSame(
            '\Foo\Bar\subnamespace\foo',
            strval($this->functionResolver->resolve($context, Symbol::fromString('subnamespace\foo')))
        );
        $this->assertSame(
            '\Foo\Bar\subnamespace\foo',
            strval($this->resolver->resolve($context, Symbol::fromString('subnamespace\foo')))
        );
        $this->assertSame(
            '\Foo\Bar\subnamespace\FOO',
            strval($this->constantResolver->resolve($context, Symbol::fromString('subnamespace\FOO')))
        );
        $this->assertSame(
            '\Foo\Bar\foo',
            strval($this->functionResolver->resolve($context, Symbol::fromString('\Foo\Bar\foo')))
        );
        $this->assertSame(
            '\Foo\Bar\foo',
            strval($this->resolver->resolve($context, Symbol::fromString('\Foo\Bar\foo')))
        );
        $this->assertSame(
            '\Foo\Bar\FOO',
            strval($this->constantResolver->resolve($context, Symbol::fromString('\Foo\Bar\FOO')))
        );
    }

    /**
     * Tests for PHP manual entry "Using namespaces: Basics".
     *
     * Example "Example #1 Accessing global classes, functions and constants from within a namespace"
     *
     * @see http://php.net/manual/en/language.namespaces.basics.php
     */
    public function testResolveDocumentationBasicsExample1()
    {
        $context = $this->contextFromSource('namespace Foo;');

        $this->assertSame(
            '\strlen',
            strval($this->functionResolver->resolve($context, Symbol::fromString('\strlen')))
        );
        $this->assertSame(
            '\INI_ALL',
            strval($this->constantResolver->resolve($context, Symbol::fromString('\INI_ALL')))
        );
        $this->assertSame(
            '\Exception',
            strval($this->resolver->resolve($context, Symbol::fromString('\Exception')))
        );
    }

    /**
     * Tests for PHP manual entry "namespace keyword and __NAMESPACE__ constant".
     *
     * Example "Example #4 the namespace operator, inside a namespace"
     *
     * @see http://php.net/manual/en/language.namespaces.nsconstants.php
     */
    public function testResolveDocumentationNamespaceKeywordExample4()
    {
        $context = $this->contextFromSource('
            namespace MyProject;

            use blah\blah as mine;
        ');

        $this->assertSame(
            '\MyProject\blah\mine',
            strval($this->functionResolver->resolve($context, Symbol::fromString('blah\mine')))
        );
        $this->assertSame(
            '\MyProject\blah\mine',
            strval($this->functionResolver->resolve($context, Symbol::fromString('namespace\blah\mine')))
        );
        $this->assertSame(
            '\MyProject\func',
            strval($this->functionResolver->resolve($context, Symbol::fromString('namespace\func')))
        );
        $this->assertSame(
            '\MyProject\sub\func',
            strval($this->functionResolver->resolve($context, Symbol::fromString('namespace\sub\func')))
        );
        $this->assertSame(
            '\MyProject\cname',
            strval($this->resolver->resolve($context, Symbol::fromString('namespace\cname')))
        );
        $this->assertSame(
            '\MyProject\sub\cname',
            strval($this->resolver->resolve($context, Symbol::fromString('namespace\sub\cname')))
        );
        $this->assertSame(
            '\MyProject\CONSTANT',
            strval($this->constantResolver->resolve($context, Symbol::fromString('namespace\CONSTANT')))
        );
    }

    /**
     * Tests for PHP manual entry "namespace keyword and __NAMESPACE__ constant".
     *
     * Example "Example #5 the namespace operator, in global code"
     *
     * @see http://php.net/manual/en/language.namespaces.nsconstants.php
     */
    public function testResolveDocumentationNamespaceKeywordExample5()
    {
        $context = $this->contextFromSource('');

        $this->assertSame(
            '\func',
            strval($this->functionResolver->resolve($context, Symbol::fromString('namespace\func')))
        );
        $this->assertSame(
            '\sub\func',
            strval($this->functionResolver->resolve($context, Symbol::fromString('namespace\sub\func')))
        );
        $this->assertSame(
            '\cname',
            strval($this->resolver->resolve($context, Symbol::fromString('namespace\cname')))
        );
        $this->assertSame(
            '\sub\cname',
            strval($this->resolver->resolve($context, Symbol::fromString('namespace\sub\cname')))
        );
        $this->assertSame(
            '\CONSTANT',
            strval($this->constantResolver->resolve($context, Symbol::fromString('namespace\CONSTANT')))
        );
    }

    /**
     * Tests for PHP manual entry "Using namespaces: Aliasing/Importing".
     *
     * Example "Example #1 importing/aliasing with the use operator"
     *
     * @see http://php.net/manual/en/language.namespaces.importing.php
     */
    public function testResolveDocumentationImportingExample1()
    {
        $context = $this->contextFromSource('
            namespace foo;

            use My\Full\Classname as Another;
            use My\Full\NSname;
            use ArrayObject;
            use function My\Full\functionName;
            use function My\Full\functionName as func;
            use const My\Full\CONSTANT;
        ');

        $this->assertSame(
            '\foo\Another',
            strval($this->resolver->resolve($context, Symbol::fromString('namespace\Another')))
        );
        $this->assertSame(
            '\My\Full\Classname',
            strval($this->resolver->resolve($context, Symbol::fromString('Another')))
        );
        $this->assertSame(
            '\My\Full\NSname\subns\func',
            strval($this->functionResolver->resolve($context, Symbol::fromString('NSname\subns\func')))
        );
        $this->assertSame(
            '\ArrayObject',
            strval($this->resolver->resolve($context, Symbol::fromString('ArrayObject')))
        );
        $this->assertSame(
            '\My\Full\functionName',
            strval($this->functionResolver->resolve($context, Symbol::fromString('func')))
        );
        $this->assertSame(
            '\My\Full\CONSTANT',
            strval($this->constantResolver->resolve($context, Symbol::fromString('CONSTANT')))
        );
    }

    /**
     * Tests for PHP manual entry "Using namespaces: Aliasing/Importing".
     *
     * Example "Example #2 importing/aliasing with the use operator, multiple use statements combined"
     *
     * @see http://php.net/manual/en/language.namespaces.importing.php
     */
    public function testResolveDocumentationImportingExample2()
    {
        $context = $this->contextFromSource('
            namespace foo;

            use My\Full\Classname as Another;
            use My\Full\NSname;
        ');

        $this->assertSame(
            '\My\Full\Classname',
            strval($this->resolver->resolve($context, Symbol::fromString('Another')))
        );
        $this->assertSame(
            '\My\Full\NSname\subns\func',
            strval($this->functionResolver->resolve($context, Symbol::fromString('NSname\subns\func')))
        );
    }

    /**
     * Tests for PHP manual entry "Using namespaces: Aliasing/Importing".
     *
     * Example "Example #4 Importing and fully qualified names"
     *
     * @see http://php.net/manual/en/language.namespaces.importing.php
     */
    public function testResolveDocumentationImportingExample4()
    {
        $context = $this->contextFromSource('
            namespace foo;

            use My\Full\Classname as Another;
            use My\Full\NSname;
        ');

        $this->assertSame(
            '\My\Full\Classname',
            strval($this->resolver->resolve($context, Symbol::fromString('Another')))
        );
        $this->assertSame(
            '\Another',
            strval($this->resolver->resolve($context, Symbol::fromString('\Another')))
        );
        $this->assertSame(
            '\My\Full\Classname\thing',
            strval($this->resolver->resolve($context, Symbol::fromString('Another\thing')))
        );
        $this->assertSame(
            '\Another\thing',
            strval($this->resolver->resolve($context, Symbol::fromString('\Another\thing')))
        );
    }

    /**
     * Tests for PHP manual entry "Global space".
     *
     * Example "Example #1 Using global space specification"
     *
     * @see http://php.net/manual/en/language.namespaces.global.php
     */
    public function testResolveDocumentationGlobalSpaceExample1()
    {
        $context = $this->contextFromSource('namespace A\B\C;');

        $this->assertSame(
            '\fopen',
            strval($this->functionResolver->resolve($context, Symbol::fromString('\fopen')))
        );
    }

    /**
     * Tests for PHP manual entry "Using namespaces: fallback to global function/constant".
     *
     * Example "Example #1 Accessing global classes inside a namespace"
     *
     * @see http://php.net/manual/en/language.namespaces.fallback.php
     */
    public function testResolveDocumentationFallbackExample1()
    {
        $context = $this->contextFromSource('namespace A\B\C;');

        $this->assertSame(
            '\A\B\C\Exception',
            strval($this->resolver->resolve($context, Symbol::fromString('Exception')))
        );
        $this->assertSame(
            '\Exception',
            strval($this->resolver->resolve($context, Symbol::fromString('\Exception')))
        );
        $this->assertSame(
            '\A\B\C\ArrayObject',
            strval($this->resolver->resolve($context, Symbol::fromString('ArrayObject')))
        );
    }

    /**
     * Tests for PHP manual entry "Using namespaces: fallback to global function/constant".
     *
     * Example "Example #2 global functions/constants fallback inside a namespace"
     *
     * @see http://php.net/manual/en/language.namespaces.fallback.php
     */
    public function testResolveDocumentationFallbackExample2()
    {
        $functionResolver = function ($functionName) {
            return 'A\B\C\strlen' === $functionName;
        };
        $constantResolver = function ($constantName) {
            return 'A\B\C\E_ERROR' === $constantName;
        };
        $this->functionResolver = new FunctionSymbolResolver($this->symbolFactory, $functionResolver);
        $this->constantResolver = new ConstantSymbolResolver($this->symbolFactory, $constantResolver);
        $context = $this->contextFromSource('namespace A\B\C;');

        $this->assertSame(
            '\A\B\C\E_ERROR',
            strval($this->constantResolver->resolve($context, Symbol::fromString('E_ERROR')))
        );
        $this->assertSame(
            '\INI_ALL',
            strval($this->constantResolver->resolve($context, Symbol::fromString('INI_ALL')))
        );
    }

    /**
     * Tests for PHP manual entry "Name resolution rules".
     *
     * Example "Example #1 Name resolutions illustrated"
     *
     * @see http://php.net/manual/en/language.namespaces.rules.php
     */
    public function testResolveDocumentationResolutionExample1()
    {
        $functionResolver = function ($functionName) {
            return true;
        };
        $constantResolver = function ($constantName) {
            return true;
        };
        $this->functionResolver = new FunctionSymbolResolver($this->symbolFactory, $functionResolver);
        $this->constantResolver = new ConstantSymbolResolver($this->symbolFactory, $constantResolver);
        $context = $this->contextFromSource('
            namespace A;

            use B\D;
            use C\E as F;
        ');

        // function calls
        $this->assertSame(
            '\A\foo',
            strval($this->functionResolver->resolve($context, Symbol::fromString('foo')))
        );
        $this->assertSame(
            '\foo',
            strval($this->functionResolver->resolve($context, Symbol::fromString('\foo')))
        );
        $this->assertSame(
            '\A\my\foo',
            strval($this->functionResolver->resolve($context, Symbol::fromString('my\foo')))
        );
        $this->assertSame(
            '\A\F',
            strval($this->functionResolver->resolve($context, Symbol::fromString('F')))
        );

        // class references
        $this->assertSame(
            '\A\B',
            strval($this->resolver->resolve($context, Symbol::fromString('B')))
        );
        $this->assertSame(
            '\B\D',
            strval($this->resolver->resolve($context, Symbol::fromString('D')))
        );
        $this->assertSame(
            '\C\E',
            strval($this->resolver->resolve($context, Symbol::fromString('F')))
        );
        $this->assertSame(
            '\B',
            strval($this->resolver->resolve($context, Symbol::fromString('\B')))
        );
        $this->assertSame(
            '\D',
            strval($this->resolver->resolve($context, Symbol::fromString('\D')))
        );
        $this->assertSame(
            '\F',
            strval($this->resolver->resolve($context, Symbol::fromString('\F')))
        );

        // static methods/namespace functions from another namespace
        $this->assertSame(
            '\A\B\foo',
            strval($this->functionResolver->resolve($context, Symbol::fromString('B\foo')))
        );
        $this->assertSame(
            '\A\B',
            strval($this->resolver->resolve($context, Symbol::fromString('B')))
        );
        $this->assertSame(
            '\B\D',
            strval($this->resolver->resolve($context, Symbol::fromString('D')))
        );
        $this->assertSame(
            '\B\foo',
            strval($this->functionResolver->resolve($context, Symbol::fromString('\B\foo')))
        );
        $this->assertSame(
            '\B',
            strval($this->resolver->resolve($context, Symbol::fromString('\B')))
        );

        // static methods/namespace functions of current namespace
        $this->assertSame(
            '\A\A\B',
            strval($this->resolver->resolve($context, Symbol::fromString('A\B')))
        );
        $this->assertSame(
            '\A\B',
            strval($this->resolver->resolve($context, Symbol::fromString('\A\B')))
        );
    }

    /**
     * Tests for PHP manual entry "FAQ: things you need to know about namespaces".
     *
     * Example "If I don't use namespaces, should I care about any of this?"
     *
     * @see http://php.net/manual/en/language.namespaces.faq.php#language.namespaces.faq.shouldicare
     */
    public function testResolveDocumentationFaqShouldICare()
    {
        $context = $this->contextFromSource('');

        $this->assertSame(
            '\stdClass',
            strval($this->resolver->resolve($context, Symbol::fromString('\stdClass')))
        );
        $this->assertSame(
            '\stdClass',
            strval($this->resolver->resolve($context, Symbol::fromString('stdClass')))
        );
    }

    /**
     * Tests for PHP manual entry "FAQ: things you need to know about namespaces".
     *
     * Example "How do I use internal or global classes in a namespace?"
     *
     * @see http://php.net/manual/en/language.namespaces.faq.php#language.namespaces.faq.globalclass
     */
    public function testResolveDocumentationFaqGlobalClass()
    {
        $context = $this->contextFromSource('namespace foo;');

        $this->assertSame(
            '\stdClass',
            strval($this->resolver->resolve($context, Symbol::fromString('\stdClass')))
        );
        $this->assertSame(
            '\ArrayObject',
            strval($this->resolver->resolve($context, Symbol::fromString('\ArrayObject')))
        );
        $this->assertSame(
            '\DirectoryIterator',
            strval($this->resolver->resolve($context, Symbol::fromString('\DirectoryIterator')))
        );
        $this->assertSame(
            '\Exception',
            strval($this->resolver->resolve($context, Symbol::fromString('\Exception')))
        );
    }

    /**
     * Tests for PHP manual entry "FAQ: things you need to know about namespaces".
     *
     * Example "How do I use namespaces classes, functions, or constants in their own namespace?"
     *
     * @see http://php.net/manual/en/language.namespaces.faq.php#language.namespaces.faq.innamespace
     */
    public function testResolveDocumentationFaqInNamespace()
    {
        $context = $this->contextFromSource('namespace foo;');

        $this->assertSame(
            '\foo\MyClass',
            strval($this->resolver->resolve($context, Symbol::fromString('MyClass')))
        );
        $this->assertSame(
            '\foo\MyClass',
            strval($this->resolver->resolve($context, Symbol::fromString('\foo\MyClass')))
        );
        $this->assertSame(
            '\foo\MyClass',
            strval($this->resolver->resolve($context, Symbol::fromString('MyClass')))
        );
        $this->assertSame(
            '\globalfunc',
            strval($this->functionResolver->resolve($context, Symbol::fromString('\globalfunc')))
        );
        $this->assertSame(
            '\INI_ALL',
            strval($this->constantResolver->resolve($context, Symbol::fromString('\INI_ALL')))
        );
    }

    /**
     * Tests for PHP manual entry "FAQ: things you need to know about namespaces".
     *
     * Example "How does a name like \my\name or \name resolve?"
     *
     * @see http://php.net/manual/en/language.namespaces.faq.php#language.namespaces.faq.full
     */
    public function testResolveDocumentationFaqFull()
    {
        $context = $this->contextFromSource('namespace foo;');

        $this->assertSame(
            '\my\name',
            strval($this->resolver->resolve($context, Symbol::fromString('\my\name')))
        );
        $this->assertSame(
            '\strlen',
            strval($this->functionResolver->resolve($context, Symbol::fromString('\strlen')))
        );
        $this->assertSame(
            '\INI_ALL',
            strval($this->constantResolver->resolve($context, Symbol::fromString('\INI_ALL')))
        );
    }

    /**
     * Tests for PHP manual entry "FAQ: things you need to know about namespaces".
     *
     * Example "How does a name like my\name resolve?"
     *
     * @see http://php.net/manual/en/language.namespaces.faq.php#language.namespaces.faq.qualified
     */
    public function testResolveDocumentationFaqQualified()
    {
        $context = $this->contextFromSource('
            namespace foo;

            use blah\blah as foo;
        ');

        $this->assertSame(
            '\foo\my\name',
            strval($this->resolver->resolve($context, Symbol::fromString('my\name')))
        );
        $this->assertSame(
            '\blah\blah\bar',
            strval($this->resolver->resolve($context, Symbol::fromString('foo\bar')))
        );
        $this->assertSame(
            '\foo\my\bar',
            strval($this->functionResolver->resolve($context, Symbol::fromString('my\bar')))
        );
        $this->assertSame(
            '\foo\my\BAR',
            strval($this->constantResolver->resolve($context, Symbol::fromString('my\BAR')))
        );
    }

    /**
     * Tests for PHP manual entry "FAQ: things you need to know about namespaces".
     *
     * Example "How does an unqualified class name like name resolve?"
     *
     * @see http://php.net/manual/en/language.namespaces.faq.php#language.namespaces.faq.shortname1
     */
    public function testResolveDocumentationFaqShortName1()
    {
        $context = $this->contextFromSource('
            namespace foo;

            use blah\blah as foo;
        ');

        $this->assertSame(
            '\foo\name',
            strval($this->resolver->resolve($context, Symbol::fromString('name')))
        );
        $this->assertSame(
            '\blah\blah',
            strval($this->resolver->resolve($context, Symbol::fromString('foo')))
        );
    }

    /**
     * Tests for PHP manual entry "FAQ: things you need to know about namespaces".
     *
     * Example "How does an unqualified function name or unqualified constant name like name resolve?"
     *
     * @see http://php.net/manual/en/language.namespaces.faq.php#language.namespaces.faq.shortname2
     */
    public function testResolveDocumentationFaqShortName2()
    {
        $functionResolver = function ($functionName) {
            return in_array($functionName, array('foo\my', 'foo\foo', 'foo\sort'), true);
        };
        $constantResolver = function ($constantName) {
            return 'foo\FOO' === $constantName;
        };
        $this->functionResolver = new FunctionSymbolResolver($this->symbolFactory, $functionResolver);
        $this->constantResolver = new ConstantSymbolResolver($this->symbolFactory, $constantResolver);
        $context = $this->contextFromSource('
            namespace foo;

            use blah\blah as foo;
        ');

        $this->assertSame(
            '\sort',
            strval($this->functionResolver->resolve($context, Symbol::fromString('\sort')))
        );
        $this->assertSame(
            '\foo\my',
            strval($this->functionResolver->resolve($context, Symbol::fromString('my')))
        );
        $this->assertSame(
            '\strlen',
            strval($this->functionResolver->resolve($context, Symbol::fromString('strlen')))
        );
        $this->assertSame(
            '\foo\sort',
            strval($this->functionResolver->resolve($context, Symbol::fromString('sort')))
        );
        $this->assertSame(
            '\foo\foo',
            strval($this->functionResolver->resolve($context, Symbol::fromString('foo')))
        );
        $this->assertSame(
            '\foo\FOO',
            strval($this->constantResolver->resolve($context, Symbol::fromString('FOO')))
        );
        $this->assertSame(
            '\INI_ALL',
            strval($this->constantResolver->resolve($context, Symbol::fromString('INI_ALL')))
        );
    }

    /**
     * Tests for PHP manual entry "FAQ: things you need to know about namespaces".
     *
     * Example "Neither functions nor constants can be imported via the use statement."
     *
     * @see http://php.net/manual/en/language.namespaces.faq.php#language.namespaces.faq.nofuncconstantuse
     */
    public function testResolveDocumentationFaqNoFunctionConstantImport()
    {
        $context = $this->contextFromSource('
            namespace mine;

            use ultra\long\ns\name;
        ');

        $this->assertSame(
            '\ultra\long\ns\name\CONSTANT',
            strval($this->constantResolver->resolve($context, Symbol::fromString('name\CONSTANT')))
        );
        $this->assertSame(
            '\ultra\long\ns\name\func',
            strval($this->functionResolver->resolve($context, Symbol::fromString('name\func')))
        );
    }

    /**
     * Tests for PHP manual entry "FAQ: things you need to know about namespaces".
     *
     * Example "Undefined Constants referenced using any backslash die with fatal error"
     *
     * @see http://php.net/manual/en/language.namespaces.faq.php#language.namespaces.faq.constants
     */
    public function testResolveDocumentationFaqConstants()
    {
        $functionResolver = function ($functionName) {
            return false;
        };
        $constantResolver = function ($constantName) {
            return false;
        };
        $this->functionResolver = new FunctionSymbolResolver($this->symbolFactory, $functionResolver);
        $this->constantResolver = new ConstantSymbolResolver($this->symbolFactory, $constantResolver);
        $context = $this->contextFromSource('namespace bar;');

        $this->assertSame(
            '\FOO',
            strval($this->constantResolver->resolve($context, Symbol::fromString('FOO')))
        );
        $this->assertSame(
            '\FOO',
            strval($this->constantResolver->resolve($context, Symbol::fromString('\FOO')))
        );
        $this->assertSame(
            '\bar\Bar\FOO',
            strval($this->constantResolver->resolve($context, Symbol::fromString('Bar\FOO')))
        );
        $this->assertSame(
            '\Bar\FOO',
            strval($this->constantResolver->resolve($context, Symbol::fromString('\Bar\FOO')))
        );
    }

    /**
     * Tests for PHP manual entry "Migrating from PHP 5.5.x to PHP 5.6.x: New features".
     *
     * Example "use function and use const"
     *
     * @see http://php.net//manual/en/migration56.new-features.php#migration56.new-features.use
     */
    public function testResolveDocumentationNewIn56UseFunctionConst()
    {
        $context = $this->contextFromSource('
            use const Name\Space\FOO;
            use function Name\Space\f;
        ');

        $this->assertSame(
            '\Name\Space\FOO',
            strval($this->constantResolver->resolve($context, Symbol::fromString('FOO')))
        );
        $this->assertSame(
            '\Name\Space\f',
            strval($this->functionResolver->resolve($context, Symbol::fromString('f')))
        );
    }

    private function contextFromSource($source)
    {
        return $this->contextReader->readFromSource('<?php ' . $source);
    }
}
