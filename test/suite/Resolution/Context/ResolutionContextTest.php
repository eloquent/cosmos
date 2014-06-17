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

use Eloquent\Cosmos\Resolution\Context\Parser\ParserPosition;
use Eloquent\Cosmos\Resolution\Context\Renderer\ResolutionContextRenderer;
use Eloquent\Cosmos\Symbol\Factory\SymbolFactory;
use Eloquent\Cosmos\Symbol\QualifiedSymbol;
use Eloquent\Cosmos\Symbol\Symbol;
use Eloquent\Cosmos\UseStatement\UseStatement;
use Eloquent\Pathogen\FileSystem\FileSystemPath;
use NamespaceA\NamespaceB\ClassA;
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

        $this->fixturePath = dirname(dirname(dirname(__DIR__))) . '/src/contexts.php';
        $this->fixtureStream = fopen($this->fixturePath, 'rb');

        require_once $this->fixturePath;
    }

    protected function tearDown()
    {
        parent::tearDown();

        fclose($this->fixtureStream);
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

    public function testFromObject()
    {
        $actual = ResolutionContext::fromObject(new ClassA);
        $expected = <<<'EOD'
namespace NamespaceA\NamespaceB;

use NamespaceD\NamespaceE\SymbolA as SymbolB;
use SymbolC as SymbolD;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testFromSymbol()
    {
        $actual = ResolutionContext::fromSymbol(Symbol::fromString('\NamespaceA\NamespaceB\ClassA'));
        $expected = <<<'EOD'
namespace NamespaceA\NamespaceB;

use NamespaceD\NamespaceE\SymbolA as SymbolB;
use SymbolC as SymbolD;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testFromFunctionSymbol()
    {
        $actual = ResolutionContext::fromFunctionSymbol(Symbol::fromString('\FunctionD'));
        $expected = <<<'EOD'
use NamespaceH\NamespaceI\SymbolI as SymbolJ;
use SymbolK as SymbolL;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testFromClass()
    {
        $actual = ResolutionContext::fromClass(new ReflectionClass('NamespaceA\NamespaceB\ClassA'));
        $expected = <<<'EOD'
namespace NamespaceA\NamespaceB;

use NamespaceD\NamespaceE\SymbolA as SymbolB;
use SymbolC as SymbolD;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testFromFunction()
    {
        $actual = ResolutionContext::fromFunction(new ReflectionFunction('FunctionD'));
        $expected = <<<'EOD'
use NamespaceH\NamespaceI\SymbolI as SymbolJ;
use SymbolK as SymbolL;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testFromFile()
    {
        $actual = ResolutionContext::fromFile(FileSystemPath::fromString($this->fixturePath));
        $expected = <<<'EOD'
namespace NamespaceA\NamespaceB;

use NamespaceD\NamespaceE\SymbolA as SymbolB;
use SymbolC as SymbolD;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testFromFileByIndex()
    {
        $actual = ResolutionContext::fromFileByIndex(FileSystemPath::fromString($this->fixturePath), 2);
        $expected = <<<'EOD'
use NamespaceH\NamespaceI\SymbolI as SymbolJ;
use SymbolK as SymbolL;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testFromFileByPosition()
    {
        $position = new ParserPosition(24, 111);
        $actual = ResolutionContext::fromFileByPosition(FileSystemPath::fromString($this->fixturePath), $position);
        $expected = <<<'EOD'
namespace NamespaceA\NamespaceB;

use NamespaceD\NamespaceE\SymbolA as SymbolB;
use SymbolC as SymbolD;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testFromStream()
    {
        $actual = ResolutionContext::fromStream($this->fixtureStream);
        $expected = <<<'EOD'
namespace NamespaceA\NamespaceB;

use NamespaceD\NamespaceE\SymbolA as SymbolB;
use SymbolC as SymbolD;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testFromStreamByIndex()
    {
        $actual = ResolutionContext::fromStreamByIndex($this->fixtureStream, 2);
        $expected = <<<'EOD'
use NamespaceH\NamespaceI\SymbolI as SymbolJ;
use SymbolK as SymbolL;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testFromStreamByPosition()
    {
        $position = new ParserPosition(24, 111);
        $actual = ResolutionContext::fromStreamByPosition($this->fixtureStream, $position);
        $expected = <<<'EOD'
namespace NamespaceA\NamespaceB;

use NamespaceD\NamespaceE\SymbolA as SymbolB;
use SymbolC as SymbolD;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
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

    public function testAccept()
    {
        $visitor = Phake::mock('Eloquent\Cosmos\Resolution\Context\ResolutionContextVisitorInterface');
        $this->context->accept($visitor);

        Phake::verify($visitor)->visitResolutionContext($this->identicalTo($this->context));
    }
}
