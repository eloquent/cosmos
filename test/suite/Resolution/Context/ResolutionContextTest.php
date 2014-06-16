<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Context;

use Eloquent\Cosmos\Resolution\Context\Renderer\ResolutionContextRenderer;
use Eloquent\Cosmos\Symbol\Factory\SymbolFactory;
use Eloquent\Cosmos\Symbol\QualifiedSymbol;
use Eloquent\Cosmos\Symbol\Symbol;
use Eloquent\Cosmos\UseStatement\UseStatement;
use Phake;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionFunction;

class ResolutionContextTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->symbolFactory = new SymbolFactory;
        $this->primaryNamespace = $this->symbolFactory->create('\VendorA\PackageA');
        $this->useStatements = array(
            new UseStatement($this->symbolFactory->create('\VendorB\PackageB')),
            new UseStatement($this->symbolFactory->create('\VendorC\PackageC')),
        );
        $this->context = new ResolutionContext($this->primaryNamespace, $this->useStatements, $this->symbolFactory);

        $this->contextRenderer = ResolutionContextRenderer::instance();
    }

    public function testConstructor()
    {
        $this->assertSame($this->primaryNamespace, $this->context->primaryNamespace());
        $this->assertSame($this->useStatements, $this->context->useStatements());
    }

    public function testConstructorDefaults()
    {
        $this->context = new ResolutionContext;

        $this->assertEquals(new QualifiedSymbol(array()), $this->context->primaryNamespace());
        $this->assertSame(array(), $this->context->useStatements());
    }

    public function testSymbolByFirstAtom()
    {
        $this->context = new ResolutionContext(
            $this->symbolFactory->create('\foo'),
            array(
                new UseStatement($this->symbolFactory->create('\My\Full\Classname'), $this->symbolFactory->create('Another')),
                new UseStatement($this->symbolFactory->create('\My\Full\NSname')),
                new UseStatement($this->symbolFactory->create('\ArrayObject')),
            )
        );

        $this->assertSame(
            '\My\Full\Classname',
            $this->context->symbolByFirstAtom($this->symbolFactory->create('Another'))->string()
        );
        $this->assertSame(
            '\My\Full\NSname',
            $this->context->symbolByFirstAtom($this->symbolFactory->create('NSname'))->string()
        );
        $this->assertSame(
            '\ArrayObject',
            $this->context->symbolByFirstAtom($this->symbolFactory->create('ArrayObject'))->string()
        );
        $this->assertNull($this->context->symbolByFirstAtom($this->symbolFactory->create('Classname')));
        $this->assertNull($this->context->symbolByFirstAtom($this->symbolFactory->create('FooClass')));
    }

    public function testFromObject()
    {
        $actual = ResolutionContext::fromObject($this);
        $expected = <<<'EOD'
namespace Eloquent\Cosmos\Resolution\Context;

use Eloquent\Cosmos\Resolution\Context\Renderer\ResolutionContextRenderer;
use Eloquent\Cosmos\Symbol\Factory\SymbolFactory;
use Eloquent\Cosmos\Symbol\QualifiedSymbol;
use Eloquent\Cosmos\Symbol\Symbol;
use Eloquent\Cosmos\UseStatement\UseStatement;
use Phake;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionFunction;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testFromSymbol()
    {
        $actual = ResolutionContext::fromSymbol(Symbol::fromRuntimeString(__CLASS__));
        $expected = <<<'EOD'
namespace Eloquent\Cosmos\Resolution\Context;

use Eloquent\Cosmos\Resolution\Context\Renderer\ResolutionContextRenderer;
use Eloquent\Cosmos\Symbol\Factory\SymbolFactory;
use Eloquent\Cosmos\Symbol\QualifiedSymbol;
use Eloquent\Cosmos\Symbol\Symbol;
use Eloquent\Cosmos\UseStatement\UseStatement;
use Phake;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionFunction;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testFromFunctionSymbol()
    {
        $actual = ResolutionContext::fromFunctionSymbol(Symbol::fromString('\FunctionA'));
        $expected = <<<'EOD'
use NamespaceA\ClassA;
use NamespaceB\ClassB as ClassC;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testFromClass()
    {
        $actual = ResolutionContext::fromClass(new ReflectionClass(__CLASS__));
        $expected = <<<'EOD'
namespace Eloquent\Cosmos\Resolution\Context;

use Eloquent\Cosmos\Resolution\Context\Renderer\ResolutionContextRenderer;
use Eloquent\Cosmos\Symbol\Factory\SymbolFactory;
use Eloquent\Cosmos\Symbol\QualifiedSymbol;
use Eloquent\Cosmos\Symbol\Symbol;
use Eloquent\Cosmos\UseStatement\UseStatement;
use Phake;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionFunction;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testFromFunction()
    {
        $actual = ResolutionContext::fromFunction(new ReflectionFunction('FunctionA'));
        $expected = <<<'EOD'
use NamespaceA\ClassA;
use NamespaceB\ClassB as ClassC;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testAccept()
    {
        $visitor = Phake::mock('Eloquent\Cosmos\Resolution\Context\ResolutionContextVisitorInterface');
        $this->context->accept($visitor);

        Phake::verify($visitor)->visitResolutionContext($this->identicalTo($this->context));
    }
}
