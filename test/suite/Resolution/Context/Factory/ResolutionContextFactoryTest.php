<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Context\Factory;

use Eloquent\Cosmos\Resolution\Context\Parser\ParserPosition;
use Eloquent\Cosmos\Resolution\Context\Parser\ResolutionContextParser;
use Eloquent\Cosmos\Resolution\Context\Renderer\ResolutionContextRenderer;
use Eloquent\Cosmos\Resolution\Context\ResolutionContext;
use Eloquent\Cosmos\Symbol\Factory\SymbolFactory;
use Eloquent\Cosmos\Symbol\Symbol;
use Eloquent\Cosmos\UseStatement\UseStatement;
use Eloquent\Liberator\Liberator;
use Eloquent\Pathogen\FileSystem\FileSystemPath;
use Icecave\Isolator\Isolator;
use NamespaceA\NamespaceB\ClassA;
use NamespaceC\ClassC;
use Phake;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionFunction;

class ResolutionContextFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->symbolFactory = new SymbolFactory;
        $this->contextParser = new ResolutionContextParser;
        $this->isolator = Phake::partialMock(Isolator::className());
        $this->factory = new ResolutionContextFactory($this->symbolFactory, $this->contextParser, $this->isolator);

        $this->primaryNamespace = $this->symbolFactory->create('\VendorA\PackageA');
        $this->useStatements = array(
            new UseStatement($this->symbolFactory->create('\VendorB\PackageB')),
            new UseStatement($this->symbolFactory->create('\VendorC\PackageC')),
        );
        $this->context = new ResolutionContext($this->primaryNamespace, $this->useStatements, $this->symbolFactory);
        $this->contextRenderer = ResolutionContextRenderer::instance();

        SymbolFactory::instance()->globalNamespace();
        $this->symbolFactory->globalNamespace();

        $this->fixturePath = __DIR__ . '/../../../../src/contexts.php';
        require_once $this->fixturePath;
    }

    public function testConstructor()
    {
        $this->assertSame($this->symbolFactory, $this->factory->symbolFactory());
        $this->assertSame($this->contextParser, $this->factory->contextParser());
    }

    public function testConstructorDefaults()
    {
        $this->factory = new ResolutionContextFactory;

        $this->assertSame(SymbolFactory::instance(), $this->factory->symbolFactory());
        $this->assertEquals(
            new ResolutionContextParser($this->symbolFactory, null, null, null, $this->factory),
            $this->factory->contextParser()
        );
    }

    public function testCreate()
    {
        $actual = $this->factory->create($this->primaryNamespace, $this->useStatements);

        $this->assertEquals($this->context, $actual);
    }

    public function testCreateFromObject()
    {
        $actual = $this->factory->createFromObject(new ClassA);
        $expected = <<<'EOD'
namespace NamespaceA\NamespaceB;

use NamespaceD\NamespaceE\SymbolA as SymbolB;
use SymbolC as SymbolD;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testCreateFromObjectSecondaryNamespace()
    {
        $actual = $this->factory->createFromObject(new ClassC);
        $expected = <<<'EOD'
namespace NamespaceC;

use NamespaceF\NamespaceG\SymbolE as SymbolF;
use SymbolG as SymbolH;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testCreateFromSymbol()
    {
        $actual = $this->factory->createFromSymbol(Symbol::fromString('\NamespaceA\NamespaceB\ClassA'));
        $expected = <<<'EOD'
namespace NamespaceA\NamespaceB;

use NamespaceD\NamespaceE\SymbolA as SymbolB;
use SymbolC as SymbolD;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testCreateFromSymbolSecondaryNamespace()
    {
        $actual = $this->factory->createFromSymbol(Symbol::fromString('\NamespaceC\ClassC'));
        $expected = <<<'EOD'
namespace NamespaceC;

use NamespaceF\NamespaceG\SymbolE as SymbolF;
use SymbolG as SymbolH;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testCreateFromSymbolWithString()
    {
        $actual = $this->factory->createFromSymbol('NamespaceA\NamespaceB\ClassA');
        $expected = <<<'EOD'
namespace NamespaceA\NamespaceB;

use NamespaceD\NamespaceE\SymbolA as SymbolB;
use SymbolC as SymbolD;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testCreateFromSymbolFailureUndefined()
    {
        $this->setExpectedException('Eloquent\Cosmos\Exception\UndefinedSymbolException', "Undefined class '\\\\Foo'.");
        $this->factory->createFromSymbol('\Foo');
    }

    public function testCreateFromFunctionSymbol()
    {
        $actual = $this->factory->createFromFunctionSymbol(Symbol::fromString('\FunctionD'));
        $expected = <<<'EOD'
use NamespaceH\NamespaceI\SymbolI as SymbolJ;
use SymbolK as SymbolL;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testCreateFromFunctionSymbolWithNamespacedFunction()
    {
        $actual = $this->factory->createFromFunctionSymbol(Symbol::fromString('\NamespaceA\NamespaceB\FunctionA'));
        $expected = <<<'EOD'
namespace NamespaceA\NamespaceB;

use NamespaceD\NamespaceE\SymbolA as SymbolB;
use SymbolC as SymbolD;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testCreateFromFunctionSymbolWithString()
    {
        $actual = $this->factory->createFromFunctionSymbol('FunctionD');
        $expected = <<<'EOD'
use NamespaceH\NamespaceI\SymbolI as SymbolJ;
use SymbolK as SymbolL;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testCreateFromFunctionSymbolFailureUndefined()
    {
        $this->setExpectedException(
            'Eloquent\Cosmos\Exception\UndefinedSymbolException',
            "Undefined function '\\\\Foo'."
        );
        $this->factory->createFromFunctionSymbol('\Foo');
    }

    public function testCreateFromClass()
    {
        $actual = $this->factory->createFromClass(new ReflectionClass('NamespaceA\NamespaceB\ClassA'));
        $expected = <<<'EOD'
namespace NamespaceA\NamespaceB;

use NamespaceD\NamespaceE\SymbolA as SymbolB;
use SymbolC as SymbolD;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testCreateFromClassSecondaryNamespace()
    {
        $actual = $this->factory->createFromClass(new ReflectionClass('NamespaceC\ClassC'));
        $expected = <<<'EOD'
namespace NamespaceC;

use NamespaceF\NamespaceG\SymbolE as SymbolF;
use SymbolG as SymbolH;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testCreateFromClassWithInbuiltClass()
    {
        $actual = $this->factory->createFromClass(new ReflectionClass('ReflectionClass'));
        $expected = '';

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testCreateFromClassFailureFileSystemOpen()
    {
        $class = Phake::mock('ReflectionClass');
        Phake::when($class)->getFileName()->thenReturn('/path/to/foo');

        $this->setExpectedException(
            'Eloquent\Cosmos\Exception\ReadException',
            "Unable to read from '/path/to/foo' (fopen(/path/to/foo): failed to open stream: No such file or directory)."
        );
        $this->factory->createFromClass($class);
    }

    public function testCreateFromClassFailureNoMatchingSymbol()
    {
        $class = Phake::mock('ReflectionClass');
        Phake::when($class)->getName()->thenReturn('Foo');
        Phake::when($class)->getFileName()->thenReturn($this->fixturePath);

        $this->setExpectedException('Eloquent\Cosmos\Exception\UndefinedSymbolException');
        $this->factory->createFromClass($class);
    }

    public function testCreateFromFunction()
    {
        $actual = $this->factory->createFromFunction(new ReflectionFunction('FunctionD'));
        $expected = <<<'EOD'
use NamespaceH\NamespaceI\SymbolI as SymbolJ;
use SymbolK as SymbolL;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testCreateFromFunctionWithNamespacedFunction()
    {
        $actual = $this->factory->createFromFunction(new ReflectionFunction('NamespaceA\NamespaceB\FunctionB'));
        $expected = <<<'EOD'
namespace NamespaceA\NamespaceB;

use NamespaceD\NamespaceE\SymbolA as SymbolB;
use SymbolC as SymbolD;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testCreateFromFunctionWithInbuiltFunction()
    {
        $actual = $this->factory->createFromFunction(new ReflectionFunction('printf'));
        $expected = '';

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testCreateFromFunctionFailureFileSystemOpen()
    {
        $function = Phake::mock('ReflectionFunction');
        Phake::when($function)->getFileName()->thenReturn('/path/to/foo');

        $this->setExpectedException(
            'Eloquent\Cosmos\Exception\ReadException',
            "Unable to read from '/path/to/foo' (fopen(/path/to/foo): failed to open stream: No such file or directory)."
        );
        $this->factory->createFromFunction($function);
    }

    public function testCreateFromFunctionFailureNoMatchingSymbol()
    {
        $function = Phake::mock('ReflectionFunction');
        Phake::when($function)->getName()->thenReturn('Foo');
        Phake::when($function)->getFileName()->thenReturn($this->fixturePath);

        $this->setExpectedException('Eloquent\Cosmos\Exception\UndefinedSymbolException');
        $this->factory->createFromFunction($function);
    }

    public function testCreateFromFile()
    {
        $actual = $this->factory->createFromFile(FileSystemPath::fromString($this->fixturePath));
        $expected = <<<'EOD'
namespace NamespaceA\NamespaceB;

use NamespaceD\NamespaceE\SymbolA as SymbolB;
use SymbolC as SymbolD;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testCreateFromFileWithString()
    {
        $actual = $this->factory->createFromFile($this->fixturePath);
        $expected = <<<'EOD'
namespace NamespaceA\NamespaceB;

use NamespaceD\NamespaceE\SymbolA as SymbolB;
use SymbolC as SymbolD;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testCreateFromFileFailureFileSystemOpen()
    {
        $this->setExpectedException(
            'Eloquent\Cosmos\Exception\ReadException',
            "Unable to read from '/path/to/foo' (fopen(/path/to/foo): failed to open stream: No such file or directory)."
        );
        $this->factory->createFromFile('/path/to/foo');
    }

    public function testCreateFromFileByIndex()
    {
        $actual = $this->factory->createFromFileByIndex(FileSystemPath::fromString($this->fixturePath), 2);
        $expected = <<<'EOD'
use NamespaceH\NamespaceI\SymbolI as SymbolJ;
use SymbolK as SymbolL;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testCreateFromFileFailureFileSystemRead()
    {
        Phake::when($this->isolator)->stream_get_contents(Phake::anyParameters())->thenReturn(false);
        Phake::when($this->isolator)->error_get_last()
            ->thenReturn(array('message' => 'stream_get_contents(): failed to read from stream'));

        $this->setExpectedException(
            'Eloquent\Cosmos\Exception\ReadException',
            "Unable to read from '/path/to/foo' (stream_get_contents(): failed to read from stream)."
        );
        $this->factory->createFromFile('/path/to/foo');
    }

    public function testCreateFromFileByIndexFailureUndefined()
    {
        $this->setExpectedException(
            'Eloquent\Cosmos\Resolution\Context\Factory\Exception\UndefinedResolutionContextException'
        );
        $this->factory->createFromFileByIndex($this->fixturePath, 3);
    }

    public function testCreateFromFileByIndexFailureFileSystemOpen()
    {
        $this->setExpectedException(
            'Eloquent\Cosmos\Exception\ReadException',
            "Unable to read from '/path/to/foo' (fopen(/path/to/foo): failed to open stream: No such file or directory)."
        );
        $this->factory->createFromFileByIndex('/path/to/foo', 0);
    }

    public function testCreateFromFileByPosition()
    {
        $position = new ParserPosition(24, 111);
        $actual = $this->factory->createFromFileByPosition(FileSystemPath::fromString($this->fixturePath), $position);
        $expected = <<<'EOD'
namespace NamespaceA\NamespaceB;

use NamespaceD\NamespaceE\SymbolA as SymbolB;
use SymbolC as SymbolD;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testCreateFromFileByPositionWithString()
    {
        $position = new ParserPosition(24, 111);
        $actual = $this->factory->createFromFileByPosition($this->fixturePath, $position);
        $expected = <<<'EOD'
namespace NamespaceA\NamespaceB;

use NamespaceD\NamespaceE\SymbolA as SymbolB;
use SymbolC as SymbolD;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testCreateFromFileByPositionSecondaryNamespace()
    {
        $position = new ParserPosition(44, 1);
        $actual = $this->factory->createFromFileByPosition(FileSystemPath::fromString($this->fixturePath), $position);
        $expected = <<<'EOD'
namespace NamespaceC;

use NamespaceF\NamespaceG\SymbolE as SymbolF;
use SymbolG as SymbolH;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testCreateFromFileByPositionBeforeFirst()
    {
        $position = new ParserPosition(1, 1);
        $actual = $this->factory->createFromFileByPosition(FileSystemPath::fromString($this->fixturePath), $position);
        $expected = '';

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testCreateFromFileByPositionAfterLast()
    {
        $position = new ParserPosition(1111, 2222);
        $actual = $this->factory->createFromFileByPosition(FileSystemPath::fromString($this->fixturePath), $position);
        $expected = <<<'EOD'
use NamespaceH\NamespaceI\SymbolI as SymbolJ;
use SymbolK as SymbolL;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testCreateFromFileByPositionFailureFileSystemOpen()
    {
        $position = new ParserPosition(1111, 2222);

        $this->setExpectedException(
            'Eloquent\Cosmos\Exception\ReadException',
            "Unable to read from '/path/to/foo' (fopen(/path/to/foo): failed to open stream: No such file or directory)."
        );
        $this->factory->createFromFileByPosition('/path/to/foo', $position);
    }

    public function testInstance()
    {
        $class = get_class($this->factory);
        $liberatedClass = Liberator::liberateClass($class);
        $liberatedClass->instance = null;
        $actual = $class::instance();

        $this->assertInstanceOf($class, $actual);
        $this->assertSame($actual, $class::instance());
    }
}
