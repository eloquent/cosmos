<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Persistence;

use Eloquent\Cosmos\Parser\ResolutionContextParser;
use Eloquent\Cosmos\Parser\TokenNormalizer;
use Eloquent\Cosmos\Resolution\Context\ResolutionContext;
use Eloquent\Cosmos\Resolution\Context\ResolutionContextFactory;
use Eloquent\Cosmos\Symbol\Symbol;
use Eloquent\Cosmos\Symbol\SymbolFactory;
use Eloquent\Cosmos\UseStatement\UseStatement;
use Eloquent\Liberator\Liberator;
use Eloquent\Phony\Phpunit\Phony;
use NamespaceA\NamespaceB\ClassA;
use NamespaceC\ClassC;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionFunction;

/**
 * @covers \Eloquent\Cosmos\Persistence\ResolutionContextReader
 */
class ResolutionContextReaderTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->tokenNormalizer = new TokenNormalizer();
        $this->contextParser = new ResolutionContextParser();
        $this->contextFactory = new ResolutionContextFactory();
        $this->symbolFactory = new SymbolFactory();
        $this->cache = Phony::mock('Eloquent\Cosmos\Cache\CacheInterface');
        $this->subject = new ResolutionContextReader(
            $this->tokenNormalizer,
            $this->contextParser,
            $this->contextFactory,
            $this->symbolFactory,
            $this->cache->mock()
        );

        $this->primaryNamespace = Symbol::fromString('\VendorA\PackageA');
        $this->useStatements = array(
            UseStatement::fromSymbol(Symbol::fromString('\VendorB\PackageB')),
            UseStatement::fromSymbol(Symbol::fromString('\VendorC\PackageC')),
        );
        $this->context = new ResolutionContext($this->primaryNamespace, $this->useStatements);

        $this->fixturePath = dirname(dirname(__DIR__)) . '/fixture/context-reader/contexts.php';
        $this->fixtureStream = fopen($this->fixturePath, 'rb');
        $this->fixtureSource = stream_get_contents($this->fixtureStream);
        fseek($this->fixtureStream, 0);

        require_once $this->fixturePath;

        $this->namespaceA = <<<'EOD'
namespace NamespaceA\NamespaceB;

use NamespaceD\NamespaceE\SymbolA as SymbolB;
use SymbolC as SymbolD;
use SymbolN as SymbolO, SymbolP;

EOD;
        $this->namespaceB = <<<'EOD'
namespace NamespaceC;

use NamespaceF\NamespaceG\SymbolE as SymbolF;
use SymbolG as SymbolH;

EOD;
        $this->namespaceC = <<<'EOD'
use NamespaceH\NamespaceI\SymbolI as SymbolJ;
use SymbolK as SymbolL;

EOD;
    }

    protected function tearDown()
    {
        fclose($this->fixtureStream);
    }

    public function testReadFromObject()
    {
        $actual = $this->subject->readFromObject(new ClassA());

        $this->assertSame($this->namespaceA, strval($actual));
    }

    public function testReadFromObjectSecondaryNamespace()
    {
        $actual = $this->subject->readFromObject(new ClassC());

        $this->assertSame($this->namespaceB, strval($actual));
    }

    public function testReadFromSymbol()
    {
        $actual = $this->subject->readFromSymbol(Symbol::fromString('\NamespaceA\NamespaceB\ClassA'));

        $this->assertSame($this->namespaceA, strval($actual));
    }

    public function testReadFromSymbolSecondaryNamespace()
    {
        $actual = $this->subject->readFromSymbol(Symbol::fromString('\NamespaceC\ClassC'));

        $this->assertSame($this->namespaceB, strval($actual));
    }

    public function testReadFromSymbolWithString()
    {
        $actual = $this->subject->readFromSymbol('NamespaceA\NamespaceB\ClassA');

        $this->assertSame($this->namespaceA, strval($actual));
    }

    public function testReadFromSymbolFailureUndefined()
    {
        $this->setExpectedException('Eloquent\Cosmos\Exception\UndefinedSymbolException', "Undefined class '\\\\Foo'.");
        $this->subject->readFromSymbol('\Foo');
    }

    public function testReadFromFunctionSymbol()
    {
        $actual = $this->subject->readFromFunctionSymbol(Symbol::fromString('\FunctionD'));

        $this->assertSame($this->namespaceC, strval($actual));
    }

    public function testReadFromFunctionSymbolWithNamespacedFunction()
    {
        $actual = $this->subject->readFromFunctionSymbol(Symbol::fromString('\NamespaceA\NamespaceB\FunctionA'));

        $this->assertSame($this->namespaceA, strval($actual));
    }

    public function testReadFromFunctionSymbolWithString()
    {
        $actual = $this->subject->readFromFunctionSymbol('FunctionD');

        $this->assertSame($this->namespaceC, strval($actual));
    }

    public function testReadFromFunctionSymbolFailureUndefined()
    {
        $this->setExpectedException(
            'Eloquent\Cosmos\Exception\UndefinedSymbolException',
            "Undefined function '\\\\Foo'."
        );
        $this->subject->readFromFunctionSymbol('\Foo');
    }

    public function testReadFromClass()
    {
        $actual = $this->subject->readFromClass(new ReflectionClass('NamespaceA\NamespaceB\ClassA'));

        $this->assertSame($this->namespaceA, strval($actual));
    }

    public function testReadFromClassSecondaryNamespace()
    {
        $actual = $this->subject->readFromClass(new ReflectionClass('NamespaceC\ClassC'));

        $this->assertSame($this->namespaceB, strval($actual));
    }

    public function testReadFromClassWithInbuiltClass()
    {
        $actual = $this->subject->readFromClass(new ReflectionClass('ReflectionClass'));

        $this->assertSame('', strval($actual));
    }

    public function testReadFromClassFailureFileSystemOpen()
    {
        $class = Phony::fullMock('ReflectionClass');
        $class->getFileName->returns('/path/to/nonexistent');

        $this->setExpectedException(
            'Eloquent\Cosmos\Exception\ReadException',
            "Unable to read from '/path/to/nonexistent'."
        );
        $this->subject->readFromClass($class->mock());
    }

    public function testReadFromClassFailureNoMatchingSymbol()
    {
        $class = Phony::fullMock('ReflectionClass');
        $class->getName->returns('Nonexistent');
        $class->getFileName->returns($this->fixturePath);

        $this->setExpectedException(
            'Eloquent\Cosmos\Exception\UndefinedSymbolException',
            "Undefined class '\\\\Nonexistent'."
        );
        $this->subject->readFromClass($class->mock());
    }

    public function testReadFromFunction()
    {
        $actual = $this->subject->readFromFunction(new ReflectionFunction('FunctionD'));

        $this->assertSame($this->namespaceC, strval($actual));
    }

    public function testReadFromFunctionWithNamespacedFunction()
    {
        $actual = $this->subject->readFromFunction(new ReflectionFunction('NamespaceA\NamespaceB\FunctionB'));

        $this->assertSame($this->namespaceA, strval($actual));
    }

    public function testReadFromFunctionWithInbuiltFunction()
    {
        $actual = $this->subject->readFromFunction(new ReflectionFunction('printf'));

        $this->assertSame('', strval($actual));
    }

    public function testReadFromFunctionFailureFileSystemOpen()
    {
        $function = Phony::fullMock('ReflectionFunction');
        $function->getFileName->returns('/path/to/nonexistent');

        $this->setExpectedException(
            'Eloquent\Cosmos\Exception\ReadException',
            "Unable to read from '/path/to/nonexistent'."
        );
        $this->subject->readFromFunction($function->mock());
    }

    public function testReadFromFunctionFailureNoMatchingSymbol()
    {
        $function = Phony::fullMock('ReflectionFunction');
        $function->getName->returns('Foo');
        $function->getFileName->returns($this->fixturePath);

        $this->setExpectedException(
            'Eloquent\Cosmos\Exception\UndefinedSymbolException',
            "Undefined function '\\\\Foo'."
        );
        $this->subject->readFromFunction($function->mock());
    }

    public function testReadFromFile()
    {
        $actual = $this->subject->readFromFile($this->fixturePath);

        $this->assertSame($this->namespaceA, strval($actual));
    }

    public function testReadFromFileFailureFileSystemOpen()
    {
        $this->setExpectedException(
            'Eloquent\Cosmos\Exception\ReadException',
            "Unable to read from '/path/to/nonexistent'."
        );
        $this->subject->readFromFile('/path/to/nonexistent');
    }

    public function testReadFromFileByIndex()
    {
        $actual = $this->subject->readFromFileByIndex($this->fixturePath, 2);

        $this->assertSame($this->namespaceC, strval($actual));
    }

    public function testReadFromFileByIndexFailureUndefined()
    {
        $this->setExpectedException(
            'Eloquent\Cosmos\Exception\UndefinedResolutionContextException',
            "No resolution context defined at index 3 in file '"
        );
        $this->subject->readFromFileByIndex($this->fixturePath, 3);
    }

    public function testReadFromFileByIndexFailureFileSystemOpen()
    {
        $this->setExpectedException(
            'Eloquent\Cosmos\Exception\ReadException',
            "Unable to read from '/path/to/nonexistent'."
        );
        $this->subject->readFromFileByIndex('/path/to/nonexistent', 0);
    }

    public function testReadFromFileByPosition()
    {
        $actual = $this->subject->readFromFileByPosition($this->fixturePath, 24, 111);

        $this->assertSame($this->namespaceA, strval($actual));
    }

    public function testReadFromFileByPositionWithoutColumn()
    {
        $actual = $this->subject->readFromFileByPosition($this->fixturePath, 38);

        $this->assertSame($this->namespaceB, strval($actual));
    }

    public function testReadFromFileByPositionSecondaryNamespace()
    {
        $actual = $this->subject->readFromFileByPosition($this->fixturePath, 37, 5);

        $this->assertSame($this->namespaceB, strval($actual));
    }

    public function testReadFromFileByPositionEndOfContext()
    {
        $actual = $this->subject->readFromFileByPosition($this->fixturePath, 37, 4);

        $this->assertSame($this->namespaceA, strval($actual));
    }

    public function testReadFromFileByPositionBeforeFirst()
    {
        $actual = $this->subject->readFromFileByPosition($this->fixturePath, 1, 1);

        $this->assertSame('', strval($actual));
    }

    public function testReadFromFileByPositionAfterLast()
    {
        $actual = $this->subject->readFromFileByPosition($this->fixturePath, 1111, 2222);

        $this->assertSame($this->namespaceC, strval($actual));
    }

    public function testReadFromFileByPositionFailureFileSystemOpen()
    {
        $this->setExpectedException(
            'Eloquent\Cosmos\Exception\ReadException',
            "Unable to read from '/path/to/nonexistent'."
        );
        $this->subject->readFromFileByPosition('/path/to/nonexistent', 1111, 2222);
    }

    public function testReadFromStream()
    {
        $actual = $this->subject->readFromStream($this->fixtureStream);

        $this->assertSame($this->namespaceA, strval($actual));
    }

    public function testReadFromStreamFailureRead()
    {
        $this->setExpectedException('Eloquent\Cosmos\Exception\ReadException', 'Unable to read from stream.');
        $this->subject->readFromStream('');
    }

    public function testReadFromStreamByIndex()
    {
        $actual = $this->subject->readFromStreamByIndex($this->fixtureStream, 2);

        $this->assertSame($this->namespaceC, strval($actual));
    }

    public function testReadFromStreamByIndexFailureUndefined()
    {
        $this->setExpectedException(
            'Eloquent\Cosmos\Exception\UndefinedResolutionContextException',
            'No resolution context defined at index 3.'
        );
        $this->subject->readFromStreamByIndex($this->fixtureStream, 3);
    }

    public function testReadFromStreamByIndexFailureRead()
    {
        $this->setExpectedException('Eloquent\Cosmos\Exception\ReadException', 'Unable to read from stream.');
        $this->subject->readFromStreamByIndex('', 0);
    }

    public function testReadFromStreamByPosition()
    {
        $actual = $this->subject->readFromStreamByPosition($this->fixtureStream, 24, 111);

        $this->assertSame($this->namespaceA, strval($actual));
    }

    public function testReadFromStreamByPositionWithoutColumn()
    {
        $actual = $this->subject->readFromStreamByPosition($this->fixtureStream, 38);

        $this->assertSame($this->namespaceB, strval($actual));
    }

    public function testReadFromStreamByPositionSecondaryNamespace()
    {
        $actual = $this->subject->readFromStreamByPosition($this->fixtureStream, 37, 5);

        $this->assertSame($this->namespaceB, strval($actual));
    }

    public function testReadFromStreamByPositionEndOfContext()
    {
        $actual = $this->subject->readFromStreamByPosition($this->fixtureStream, 37, 4);

        $this->assertSame($this->namespaceA, strval($actual));
    }

    public function testReadFromStreamByPositionBeforeFirst()
    {
        $actual = $this->subject->readFromStreamByPosition($this->fixtureStream, 1, 1);

        $this->assertSame('', strval($actual));
    }

    public function testReadFromStreamByPositionAfterLast()
    {
        $actual = $this->subject->readFromStreamByPosition($this->fixtureStream, 1111, 2222);

        $this->assertSame($this->namespaceC, strval($actual));
    }

    public function testReadFromStreamByPositionFailureRead()
    {
        $this->setExpectedException('Eloquent\Cosmos\Exception\ReadException', 'Unable to read from stream.');
        $this->subject->readFromStreamByPosition('', 1);
    }

    public function testReadFromSource()
    {
        $actual = $this->subject->readFromSource($this->fixtureSource);

        $this->assertSame($this->namespaceA, strval($actual));
    }

    public function testReadFromSourceByIndex()
    {
        $actual = $this->subject->readFromSourceByIndex($this->fixtureSource, 2);

        $this->assertSame($this->namespaceC, strval($actual));
    }

    public function testReadFromSourceByIndexFailureUndefined()
    {
        $this->setExpectedException(
            'Eloquent\Cosmos\Exception\UndefinedResolutionContextException',
            'No resolution context defined at index 3.'
        );
        $this->subject->readFromSourceByIndex($this->fixtureSource, 3);
    }

    public function testReadFromSourceByPosition()
    {
        $actual = $this->subject->readFromSourceByPosition($this->fixtureSource, 24, 111);

        $this->assertSame($this->namespaceA, strval($actual));
    }

    public function testReadFromSourceByPositionWithoutColumn()
    {
        $actual = $this->subject->readFromSourceByPosition($this->fixtureSource, 38);

        $this->assertSame($this->namespaceB, strval($actual));
    }

    public function testReadFromSourceByPositionSecondaryNamespace()
    {
        $actual = $this->subject->readFromSourceByPosition($this->fixtureSource, 37, 5);

        $this->assertSame($this->namespaceB, strval($actual));
    }

    public function testReadFromSourceByPositionEndOfContext()
    {
        $actual = $this->subject->readFromSourceByPosition($this->fixtureSource, 37, 4);

        $this->assertSame($this->namespaceA, strval($actual));
    }

    public function testReadFromSourceByPositionBeforeFirst()
    {
        $actual = $this->subject->readFromSourceByPosition($this->fixtureSource, 1, 1);

        $this->assertSame('', strval($actual));
    }

    public function testReadFromSourceByPositionAfterLast()
    {
        $actual = $this->subject->readFromSourceByPosition($this->fixtureSource, 1111, 2222);

        $this->assertSame($this->namespaceC, strval($actual));
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
