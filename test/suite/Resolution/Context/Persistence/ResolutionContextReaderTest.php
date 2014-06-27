<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Context\Persistence;

use Eloquent\Cosmos\Resolution\Context\Factory\ResolutionContextFactory;
use Eloquent\Cosmos\Resolution\Context\Parser\ParserPosition;
use Eloquent\Cosmos\Resolution\Context\Parser\ResolutionContextParser;
use Eloquent\Cosmos\Resolution\Context\Renderer\ResolutionContextRenderer;
use Eloquent\Cosmos\Resolution\Context\ResolutionContext;
use Eloquent\Cosmos\Symbol\Factory\SymbolFactory;
use Eloquent\Cosmos\Symbol\Symbol;
use Eloquent\Cosmos\UseStatement\UseStatement;
use Eloquent\Liberator\Liberator;
use Icecave\Isolator\Isolator;
use NamespaceA\NamespaceB\ClassA;
use NamespaceC\ClassC;
use Phake;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionFunction;

class ResolutionContextReaderTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->contextParser = new ResolutionContextParser;
        $this->contextFactory = new ResolutionContextFactory;
        $this->symbolFactory = new SymbolFactory;
        $this->isolator = Phake::partialMock(Isolator::className());
        $this->reader = new ResolutionContextReader(
            $this->contextParser,
            $this->contextFactory,
            $this->symbolFactory,
            $this->isolator
        );

        $this->primaryNamespace = Symbol::fromString('\VendorA\PackageA');
        $this->useStatements = array(
            UseStatement::create(Symbol::fromString('\VendorB\PackageB')),
            UseStatement::create(Symbol::fromString('\VendorC\PackageC')),
        );
        $this->context = new ResolutionContext($this->primaryNamespace, $this->useStatements, $this->symbolFactory);
        $this->contextRenderer = ResolutionContextRenderer::instance();

        SymbolFactory::instance()->globalNamespace();
        $this->symbolFactory->globalNamespace();

        $this->fixturePath = dirname(dirname(dirname(dirname(__DIR__)))) . '/src/contexts.php';
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
        $this->assertSame($this->contextParser, $this->reader->contextParser());
        $this->assertSame($this->contextFactory, $this->reader->contextFactory());
        $this->assertSame($this->symbolFactory, $this->reader->symbolFactory());
    }

    public function testConstructorDefaults()
    {
        $this->reader = new ResolutionContextReader;

        $this->assertSame(ResolutionContextParser::instance(), $this->reader->contextParser());
        $this->assertSame(ResolutionContextFactory::instance(), $this->reader->contextFactory());
        $this->assertSame(SymbolFactory::instance(), $this->reader->symbolFactory());
    }

    public function testReadFromObject()
    {
        $actual = $this->reader->readFromObject(new ClassA);
        $expected = <<<'EOD'
namespace NamespaceA\NamespaceB;

use NamespaceD\NamespaceE\SymbolA as SymbolB;
use SymbolC as SymbolD;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testReadFromObjectSecondaryNamespace()
    {
        $actual = $this->reader->readFromObject(new ClassC);
        $expected = <<<'EOD'
namespace NamespaceC;

use NamespaceF\NamespaceG\SymbolE as SymbolF;
use SymbolG as SymbolH;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testReadFromSymbol()
    {
        $actual = $this->reader->readFromSymbol(Symbol::fromString('\NamespaceA\NamespaceB\ClassA'));
        $expected = <<<'EOD'
namespace NamespaceA\NamespaceB;

use NamespaceD\NamespaceE\SymbolA as SymbolB;
use SymbolC as SymbolD;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testReadFromSymbolSecondaryNamespace()
    {
        $actual = $this->reader->readFromSymbol(Symbol::fromString('\NamespaceC\ClassC'));
        $expected = <<<'EOD'
namespace NamespaceC;

use NamespaceF\NamespaceG\SymbolE as SymbolF;
use SymbolG as SymbolH;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testReadFromSymbolWithString()
    {
        $actual = $this->reader->readFromSymbol('NamespaceA\NamespaceB\ClassA');
        $expected = <<<'EOD'
namespace NamespaceA\NamespaceB;

use NamespaceD\NamespaceE\SymbolA as SymbolB;
use SymbolC as SymbolD;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testReadFromSymbolFailureUndefined()
    {
        $this->setExpectedException('Eloquent\Cosmos\Exception\UndefinedSymbolException', "Undefined class '\\\\Foo'.");
        $this->reader->readFromSymbol('\Foo');
    }

    public function testReadFromFunctionSymbol()
    {
        $actual = $this->reader->readFromFunctionSymbol(Symbol::fromString('\FunctionD'));
        $expected = <<<'EOD'
use NamespaceH\NamespaceI\SymbolI as SymbolJ;
use SymbolK as SymbolL;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testReadFromFunctionSymbolWithNamespacedFunction()
    {
        $actual = $this->reader->readFromFunctionSymbol(Symbol::fromString('\NamespaceA\NamespaceB\FunctionA'));
        $expected = <<<'EOD'
namespace NamespaceA\NamespaceB;

use NamespaceD\NamespaceE\SymbolA as SymbolB;
use SymbolC as SymbolD;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testReadFromFunctionSymbolWithString()
    {
        $actual = $this->reader->readFromFunctionSymbol('FunctionD');
        $expected = <<<'EOD'
use NamespaceH\NamespaceI\SymbolI as SymbolJ;
use SymbolK as SymbolL;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testReadFromFunctionSymbolFailureUndefined()
    {
        $this->setExpectedException(
            'Eloquent\Cosmos\Exception\UndefinedSymbolException',
            "Undefined function '\\\\Foo'."
        );
        $this->reader->readFromFunctionSymbol('\Foo');
    }

    public function testReadFromClass()
    {
        $actual = $this->reader->readFromClass(new ReflectionClass('NamespaceA\NamespaceB\ClassA'));
        $expected = <<<'EOD'
namespace NamespaceA\NamespaceB;

use NamespaceD\NamespaceE\SymbolA as SymbolB;
use SymbolC as SymbolD;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testReadFromClassSecondaryNamespace()
    {
        $actual = $this->reader->readFromClass(new ReflectionClass('NamespaceC\ClassC'));
        $expected = <<<'EOD'
namespace NamespaceC;

use NamespaceF\NamespaceG\SymbolE as SymbolF;
use SymbolG as SymbolH;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testReadFromClassWithInbuiltClass()
    {
        $actual = $this->reader->readFromClass(new ReflectionClass('ReflectionClass'));
        $expected = '';

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testReadFromClassFailureFileSystemOpen()
    {
        $class = Phake::mock('ReflectionClass');
        Phake::when($class)->getFileName()->thenReturn('/path/to/foo');

        $this->setExpectedException('Eloquent\Cosmos\Exception\ReadException');
        $this->reader->readFromClass($class);
    }

    public function testReadFromClassFailureNoMatchingSymbol()
    {
        $class = Phake::mock('ReflectionClass');
        Phake::when($class)->getName()->thenReturn('Foo');
        Phake::when($class)->getFileName()->thenReturn($this->fixturePath);

        $this->setExpectedException('Eloquent\Cosmos\Exception\UndefinedSymbolException');
        $this->reader->readFromClass($class);
    }

    public function testReadFromFunction()
    {
        $actual = $this->reader->readFromFunction(new ReflectionFunction('FunctionD'));
        $expected = <<<'EOD'
use NamespaceH\NamespaceI\SymbolI as SymbolJ;
use SymbolK as SymbolL;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testReadFromFunctionWithNamespacedFunction()
    {
        $actual = $this->reader->readFromFunction(new ReflectionFunction('NamespaceA\NamespaceB\FunctionB'));
        $expected = <<<'EOD'
namespace NamespaceA\NamespaceB;

use NamespaceD\NamespaceE\SymbolA as SymbolB;
use SymbolC as SymbolD;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testReadFromFunctionWithInbuiltFunction()
    {
        $actual = $this->reader->readFromFunction(new ReflectionFunction('printf'));
        $expected = '';

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testReadFromFunctionFailureFileSystemOpen()
    {
        $function = Phake::mock('ReflectionFunction');
        Phake::when($function)->getFileName()->thenReturn('/path/to/foo');

        $this->setExpectedException('Eloquent\Cosmos\Exception\ReadException');
        $this->reader->readFromFunction($function);
    }

    public function testReadFromFunctionFailureNoMatchingSymbol()
    {
        $function = Phake::mock('ReflectionFunction');
        Phake::when($function)->getName()->thenReturn('Foo');
        Phake::when($function)->getFileName()->thenReturn($this->fixturePath);

        $this->setExpectedException('Eloquent\Cosmos\Exception\UndefinedSymbolException');
        $this->reader->readFromFunction($function);
    }

    public function testReadFromFile()
    {
        $actual = $this->reader->readFromFile($this->fixturePath);
        $expected = <<<'EOD'
namespace NamespaceA\NamespaceB;

use NamespaceD\NamespaceE\SymbolA as SymbolB;
use SymbolC as SymbolD;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testReadFromFileFailureFileSystemOpen()
    {
        $this->setExpectedException('Eloquent\Cosmos\Exception\ReadException');
        $this->reader->readFromFile('/path/to/foo');
    }

    public function testReadFromFileFailureFileSystemRead()
    {
        Phake::when($this->isolator)->stream_get_contents(Phake::anyParameters())->thenReturn(false);
        Phake::when($this->isolator)->error_get_last()->thenReturn(
            array(
                'type' => E_WARNING,
                'message' => 'stream_get_contents(): unable to read from stream',
                'file' => '/path/to/file',
                'line' => 111,
            )
        );

        $this->setExpectedException(
            'Eloquent\Cosmos\Exception\ReadException',
            "Unable to read from '" . $this->fixturePath . "': stream_get_contents(): unable to read from stream"
        );
        $this->reader->readFromFile($this->fixturePath);
    }

    public function testReadFromFileFailureFileSystemReadNoLastError()
    {
        Phake::when($this->isolator)->stream_get_contents(Phake::anyParameters())->thenReturn(false);
        Phake::when($this->isolator)->error_get_last()->thenReturn(null);

        $this->setExpectedException('Eloquent\Cosmos\Exception\ReadException');
        $this->reader->readFromFile($this->fixturePath);
    }

    public function testReadFromFileByIndex()
    {
        $actual = $this->reader->readFromFileByIndex($this->fixturePath, 2);
        $expected = <<<'EOD'
use NamespaceH\NamespaceI\SymbolI as SymbolJ;
use SymbolK as SymbolL;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testReadFromFileByIndexFailureUndefined()
    {
        $this->setExpectedException(
            'Eloquent\Cosmos\Resolution\Context\Persistence\Exception\UndefinedResolutionContextException'
        );
        $this->reader->readFromFileByIndex($this->fixturePath, 3);
    }

    public function testReadFromFileByIndexFailureFileSystemOpen()
    {
        $this->setExpectedException('Eloquent\Cosmos\Exception\ReadException');
        $this->reader->readFromFileByIndex('/path/to/foo', 0);
    }

    public function testReadFromFileByPosition()
    {
        $position = new ParserPosition(24, 111);
        $actual = $this->reader->readFromFileByPosition($this->fixturePath, $position);
        $expected = <<<'EOD'
namespace NamespaceA\NamespaceB;

use NamespaceD\NamespaceE\SymbolA as SymbolB;
use SymbolC as SymbolD;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testReadFromFileByPositionWithString()
    {
        $position = new ParserPosition(24, 111);
        $actual = $this->reader->readFromFileByPosition($this->fixturePath, $position);
        $expected = <<<'EOD'
namespace NamespaceA\NamespaceB;

use NamespaceD\NamespaceE\SymbolA as SymbolB;
use SymbolC as SymbolD;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testReadFromFileByPositionSecondaryNamespace()
    {
        $position = new ParserPosition(36, 1);
        $actual = $this->reader->readFromFileByPosition($this->fixturePath, $position);
        $expected = <<<'EOD'
namespace NamespaceC;

use NamespaceF\NamespaceG\SymbolE as SymbolF;
use SymbolG as SymbolH;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testReadFromFileByPositionBeforeFirst()
    {
        $position = new ParserPosition(1, 1);
        $actual = $this->reader->readFromFileByPosition($this->fixturePath, $position);
        $expected = '';

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testReadFromFileByPositionAfterLast()
    {
        $position = new ParserPosition(1111, 2222);
        $actual = $this->reader->readFromFileByPosition($this->fixturePath, $position);
        $expected = <<<'EOD'
use NamespaceH\NamespaceI\SymbolI as SymbolJ;
use SymbolK as SymbolL;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testReadFromFileByPositionFailureFileSystemOpen()
    {
        $position = new ParserPosition(1111, 2222);

        $this->setExpectedException('Eloquent\Cosmos\Exception\ReadException');
        $this->reader->readFromFileByPosition('/path/to/foo', $position);
    }

    public function testReadFromStream()
    {
        $actual = $this->reader->readFromStream($this->fixtureStream);
        $expected = <<<'EOD'
namespace NamespaceA\NamespaceB;

use NamespaceD\NamespaceE\SymbolA as SymbolB;
use SymbolC as SymbolD;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testReadFromStreamFailureFileSystemRead()
    {
        Phake::when($this->isolator)->stream_get_contents(Phake::anyParameters())->thenReturn(false);
        Phake::when($this->isolator)->error_get_last()->thenReturn(
            array(
                'type' => E_WARNING,
                'message' => 'stream_get_contents(): unable to read from stream',
                'file' => '/path/to/file',
                'line' => 111,
            )
        );

        $this->setExpectedException('Eloquent\Cosmos\Exception\ReadException');
        $this->reader->readFromStream($this->fixtureStream);
    }

    public function testReadFromStreamByIndex()
    {
        $actual = $this->reader->readFromStreamByIndex($this->fixtureStream, 2);
        $expected = <<<'EOD'
use NamespaceH\NamespaceI\SymbolI as SymbolJ;
use SymbolK as SymbolL;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testReadFromStreamByIndexFailureUndefined()
    {
        $this->setExpectedException(
            'Eloquent\Cosmos\Resolution\Context\Persistence\Exception\UndefinedResolutionContextException'
        );
        $this->reader->readFromStreamByIndex($this->fixtureStream, 3);
    }

    public function testReadFromStreamByIndexFailureFileSystemRead()
    {
        Phake::when($this->isolator)->stream_get_contents(Phake::anyParameters())->thenReturn(false);
        Phake::when($this->isolator)->error_get_last()->thenReturn(
            array(
                'type' => E_WARNING,
                'message' => 'stream_get_contents(): unable to read from stream',
                'file' => '/path/to/file',
                'line' => 111,
            )
        );

        $this->setExpectedException('Eloquent\Cosmos\Exception\ReadException');
        $this->reader->readFromStreamByIndex($this->fixtureStream, 0);
    }

    public function testReadFromStreamByPosition()
    {
        $position = new ParserPosition(24, 111);
        $actual = $this->reader->readFromStreamByPosition($this->fixtureStream, $position);
        $expected = <<<'EOD'
namespace NamespaceA\NamespaceB;

use NamespaceD\NamespaceE\SymbolA as SymbolB;
use SymbolC as SymbolD;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testReadFromStreamByPositionSecondaryNamespace()
    {
        $position = new ParserPosition(36, 1);
        $actual = $this->reader->readFromStreamByPosition($this->fixtureStream, $position);
        $expected = <<<'EOD'
namespace NamespaceC;

use NamespaceF\NamespaceG\SymbolE as SymbolF;
use SymbolG as SymbolH;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testReadFromStreamByPositionBeforeFirst()
    {
        $position = new ParserPosition(1, 1);
        $actual = $this->reader->readFromStreamByPosition($this->fixtureStream, $position);
        $expected = '';

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testReadFromStreamByPositionAfterLast()
    {
        $position = new ParserPosition(1111, 2222);
        $actual = $this->reader->readFromStreamByPosition($this->fixtureStream, $position);
        $expected = <<<'EOD'
use NamespaceH\NamespaceI\SymbolI as SymbolJ;
use SymbolK as SymbolL;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testReadFromStreamByPositionFailureFileSystemRead()
    {
        $position = new ParserPosition(1111, 2222);
        Phake::when($this->isolator)->stream_get_contents(Phake::anyParameters())->thenReturn(false);
        Phake::when($this->isolator)->error_get_last()->thenReturn(
            array(
                'type' => E_WARNING,
                'message' => 'stream_get_contents(): unable to read from stream',
                'file' => '/path/to/file',
                'line' => 111,
            )
        );

        $this->setExpectedException('Eloquent\Cosmos\Exception\ReadException');
        $this->reader->readFromFileByPosition('/path/to/foo', $position);
    }

    public function testInstance()
    {
        $class = get_class($this->reader);
        $liberatedClass = Liberator::liberateClass($class);
        $liberatedClass->instance = null;
        $actual = $class::instance();

        $this->assertInstanceOf($class, $actual);
        $this->assertSame($actual, $class::instance());
    }
}
