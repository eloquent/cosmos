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
use Eloquent\Cosmos\Resolution\Context\ResolutionContext;
use Eloquent\Cosmos\Resolution\Context\ResolutionContextGenerator;
use Eloquent\Cosmos\Resolution\FunctionSymbolResolver;
use Eloquent\Cosmos\Resolution\SymbolReferenceGenerator;
use Eloquent\Cosmos\Resolution\SymbolResolver;
use Eloquent\Cosmos\Symbol\Symbol;
use Eloquent\Cosmos\UseStatement\UseStatement;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionObject;

/**
 * @coversNothing
 */
class DocumentationTest extends PHPUnit_Framework_TestCase
{
    public function testResolutionContextReading()
    {
        $path = __FILE__;
        $stream = fopen($path, 'r');
        $source = stream_get_contents($stream);
        fseek($stream, 0);

        $reader = ResolutionContextReader::instance();

        // from an object instance
        $context = $reader->readFromObject($this);

        // from a symbol
        $context = $reader->readFromSymbol(__CLASS__);

        // from a function symbol
        // $context = $reader->readFromFunctionSymbol(__FUNCTION__);

        // from a class reflector
        $context = $reader->readFromClass(new ReflectionClass(__CLASS__));

        // from an object reflector
        $context = $reader->readFromClass(new ReflectionObject($this));

        // from a function reflector
        // $context = $reader->readFromFunction(new ReflectionFunction(__FUNCTION__));

        // from the first context in a file
        $context = $reader->readFromFile($path);

        // from the nth context in a file
        $context = $reader->readFromFileByIndex($path, 0);

        // from a line and column number in a file
        $context = $reader->readFromFileByPosition($path, 11, 22);

        // from the first context in a stream
        $context = $reader->readFromStream($stream);

        // from the nth context in a stream
        $context = $reader->readFromStreamByIndex($stream, 0);

        // from a line and column number in a stream
        $context = $reader->readFromStreamByPosition($stream, 11, 22);

        // from the first context in source code
        $context = $reader->readFromSource($source);

        // from the nth context in source code
        $context = $reader->readFromSourceByIndex($source, 0);

        // from a line and column number in source code
        $context = $reader->readFromSourceByPosition($source, 11, 22);
    }

    public function testManualContextCreation()
    {
        $context = ResolutionContext::create(
            Symbol::fromString('\NamespaceA\NamespaceB'),
            array(
                UseStatement::fromSymbol(Symbol::fromString('\NamespaceC\ClassA')),
                UseStatement::fromSymbol(
                    Symbol::fromString('\NamespaceD\ClassA'),
                    'ClassB'
                ),
                UseStatement::fromSymbol(
                    Symbol::fromString('\NamespaceE\functionA'),
                    null,
                    'function'
                ),
            )
        );

        $actual = $context;

        $reader = ResolutionContextReader::instance();

        $context = $reader->readFromSource('<?php
            namespace NamespaceA\NamespaceB;

            use NamespaceC\ClassA;
            use NamespaceD\ClassA as ClassB;
            use function NamespaceE\functionA;
        ');

        $expected = $context;

        $this->assertSame(strval($expected), strval($actual));
    }

    public function testSymbolResolving()
    {
        $this->expectOutputString(
            implode(
                "\n",
                array(
                    '\NamespaceC\SymbolA',
                    '\NamespaceD\SymbolB',
                    '\NamespaceA\NamespaceB\SymbolX',
                    '\NamespaceC\SymbolA\SymbolX\SymbolY',
                    '\SymbolA',
                    '\NamespaceA\NamespaceB\SymbolA',
                    '\NamespaceE\functionA',
                )
            ) . "\n"
        );

        $reader = ResolutionContextReader::instance();

        $context = $reader->readFromSource('<?php
            namespace NamespaceA\NamespaceB;

            use Eloquent\Cosmos\Persistence\ResolutionContextReader;
            use Eloquent\Cosmos\Resolution\FunctionSymbolResolver;
            use Eloquent\Cosmos\Resolution\SymbolResolver;
            use Eloquent\Cosmos\Symbol\Symbol;
            use NamespaceC\SymbolA;
            use NamespaceD\SymbolB as SymbolC;
            use function NamespaceE\functionA;
        ');

        $reader = ResolutionContextReader::instance();
        $resolver = SymbolResolver::instance();

        // read the first context in this file
        // $context = $reader->readFromFile(__FILE__);

        $symbol = Symbol::fromString('SymbolA');
        $resolved = $resolver->resolve($context, $symbol);
        echo $resolved; // outputs '\NamespaceC\SymbolA'
        echo "\n";

        $symbol = Symbol::fromString('SymbolC');
        $resolved = $resolver->resolve($context, $symbol);
        echo $resolved; // outputs '\NamespaceD\SymbolB'
        echo "\n";

        $symbol = Symbol::fromString('SymbolX');
        $resolved = $resolver->resolve($context, $symbol);
        echo $resolved; // outputs '\NamespaceA\NamespaceB\SymbolX'
        echo "\n";

        $symbol = Symbol::fromString('SymbolA\SymbolX\SymbolY');
        $resolved = $resolver->resolve($context, $symbol);
        echo $resolved; // outputs '\NamespaceC\SymbolA\SymbolX\SymbolY'
        echo "\n";

        $symbol = Symbol::fromString('\SymbolA');
        $resolved = $resolver->resolve($context, $symbol);
        echo $resolved; // outputs '\SymbolA'
        echo "\n";

        $symbol = Symbol::fromString('namespace\SymbolA'); // yes, this is a real thing
        $resolved = $resolver->resolve($context, $symbol);
        echo $resolved; // outputs '\NamespaceA\NamespaceB\SymbolA'
        echo "\n";

        $resolver = FunctionSymbolResolver::instance();

        $symbol = Symbol::fromString('functionA');
        $resolved = $resolver->resolve($context, $symbol);
        echo $resolved; // outputs '\NamespaceE\functionA' (assuming the function exists)
        echo "\n";
    }

    public function testContextGeneration()
    {
        $generator = ResolutionContextGenerator::instance();
        $context = $generator->generateContext(
            Symbol::fromString('\NamespaceA\NamespaceB'),
            array(
                Symbol::fromString('\NamespaceA\NamespaceB\ClassA'),
                Symbol::fromString('\NamespaceA\NamespaceB\NamespaceC\ClassB'),
                Symbol::fromString('\NamespaceD\NamespaceE\ClassC'),
                Symbol::fromString('\NamespaceD\NamespaceF\ClassC'),
                Symbol::fromString('\ClassD'),
            )
        );

        $reader = ResolutionContextReader::instance();

        $expected = $reader->readFromSource('<?php
            namespace NamespaceA\NamespaceB;

            use ClassD;
            use NamespaceA\NamespaceB\NamespaceC\ClassB;
            use NamespaceD\NamespaceE\ClassC as NamespaceEClassC;
            use NamespaceD\NamespaceF\ClassC as NamespaceFClassC;
        ');

        $this->assertSame(strval($expected), strval($context));
    }

    public function testReferenceGeneration()
    {
        $this->expectOutputString(
            implode(
                "\n",
                array(
                    'SymbolD',
                    'SymbolA',
                    'SymbolC',
                    'SymbolC\SymbolD',
                    'namespace\SymbolA',
                    '\NamespaceA\NamespaceE\SymbolD',
                )
            ) . "\n"
        );

        $reader = ResolutionContextReader::instance();
        $referenceGenerator = SymbolReferenceGenerator::instance();

        $context = $reader->readFromSource('<?php
            namespace NamespaceA\NamespaceB;

            use NamespaceC\SymbolA;
            use NamespaceD\SymbolB as SymbolC;
            use function NamespaceE\functionA;
        ');

        $symbol = Symbol::fromString('\NamespaceA\NamespaceB\SymbolD');
        $reference = $referenceGenerator->referenceTo($context, $symbol);
        echo $reference; // outputs 'SymbolD'
        echo "\n";

        $symbol = Symbol::fromString('\NamespaceC\SymbolA');
        $reference = $referenceGenerator->referenceTo($context, $symbol);
        echo $reference; // outputs 'SymbolA'
        echo "\n";

        $symbol = Symbol::fromString('\NamespaceD\SymbolB');
        $reference = $referenceGenerator->referenceTo($context, $symbol);
        echo $reference; // outputs 'SymbolC'
        echo "\n";

        $symbol = Symbol::fromString('\NamespaceD\SymbolB\SymbolD');
        $reference = $referenceGenerator->referenceTo($context, $symbol);
        echo $reference; // outputs 'SymbolC\SymbolD'
        echo "\n";

        $symbol = Symbol::fromString('\NamespaceA\NamespaceB\SymbolA');
        $reference = $referenceGenerator->referenceTo($context, $symbol);
        echo $reference; // outputs 'namespace\SymbolA'
        echo "\n";

        $symbol = Symbol::fromString('\NamespaceA\NamespaceE\SymbolD');
        $reference = $referenceGenerator->referenceTo($context, $symbol);
        echo $reference; // outputs '\NamespaceA\NamespaceE\SymbolD'
        echo "\n";
    }
}
