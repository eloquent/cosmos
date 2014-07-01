<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos;

use Eloquent\Cosmos\Resolution\Context\Parser\ParserPosition;
use Eloquent\Cosmos\Resolution\Context\ResolutionContext;
use Eloquent\Cosmos\Symbol\Symbol;
use Eloquent\Cosmos\Symbol\SymbolType;
use Eloquent\Cosmos\UseStatement\UseStatement;
use Eloquent\Cosmos\UseStatement\UseStatementType;
use NamespaceA\ClassA;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionFunction;
use ReflectionObject;

class DocumentationTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        require_once __DIR__ . '/../src/documentation-fixtures.php';
    }

    public function testResolveAgainstNamespace()
    {
        $this->expectOutputString(
            '\Psr\Log\NullLogger\Psr\Log\NullLogger' .
            '\Psr\HttpMessage\MessageInterface\Psr\HttpMessage\MessageInterface' .
            '\ArrayObject'
        );

$namespace = Symbol::fromString('\Psr\Log');

$symbol = Symbol::fromString('NullLogger');
echo $namespace->resolve($symbol);        // outputs '\Psr\Log\NullLogger'
echo $symbol->resolveAgainst($namespace); // outputs '\Psr\Log\NullLogger'

$symbol = Symbol::fromString('..\HttpMessage\MessageInterface');
echo $namespace->resolve($symbol)->normalize();        // outputs '\Psr\HttpMessage\MessageInterface'
echo $symbol->resolveAgainst($namespace)->normalize(); // outputs '\Psr\HttpMessage\MessageInterface'

$symbol = Symbol::fromString('\ArrayObject');
echo $namespace->resolve($symbol); // outputs '\ArrayObject'
    }

    public function testResolveAgainstContext()
    {
        $this->expectOutputString(
            '\NamespaceC\SymbolA\NamespaceC\SymbolA' .
            '\SymbolA' .
            '\NamespaceA\NamespaceB\SymbolB\NamespaceA\NamespaceB\SymbolB' .
            '\NamespaceD\SymbolB\NamespaceD\SymbolB' .
            '\NamespaceD\SymbolB\SymbolE\SymbolF\NamespaceD\SymbolB\SymbolE\SymbolF' .
            '\NamespaceA\NamespaceB\SymbolA\NamespaceA\NamespaceB\SymbolA' .
            '\NamespaceA\SymbolA\NamespaceA\SymbolA' .
            '\NamespaceA\NamespaceB\SymbolD\NamespaceA\NamespaceB\SymbolD' .
            '\NamespaceE\SymbolD\NamespaceE\SymbolD'
        );

$context = new ResolutionContext(
    Symbol::fromString('\NamespaceA\NamespaceB'),
    array(
        // basic use statement
        UseStatement::create(Symbol::fromString('\NamespaceC\SymbolA')),

        // use statement with alias
        UseStatement::create(
            Symbol::fromString('\NamespaceD\SymbolB'),
            Symbol::fromString('SymbolC')
        ),

        // use function statement (PHP 5.6)
        UseStatement::create(
            Symbol::fromString('\NamespaceE\SymbolD'),
            null,
            UseStatementType::FUNCT1ON()
        ),
    )
);

$symbol = Symbol::fromString('SymbolA');
echo $context->resolve($symbol);               // outputs '\NamespaceC\SymbolA'
echo $symbol->resolveAgainstContext($context); // outputs '\NamespaceC\SymbolA'

$symbol = Symbol::fromString('\SymbolA');
echo $context->resolve($symbol);               // outputs '\SymbolA'

$symbol = Symbol::fromString('SymbolB');
echo $context->resolve($symbol);               // outputs '\NamespaceA\NamespaceB\SymbolB'
echo $symbol->resolveAgainstContext($context); // outputs '\NamespaceA\NamespaceB\SymbolB'

$symbol = Symbol::fromString('SymbolC');
echo $context->resolve($symbol);               // outputs '\NamespaceD\SymbolB'
echo $symbol->resolveAgainstContext($context); // outputs '\NamespaceD\SymbolB'

$symbol = Symbol::fromString('SymbolC\SymbolE\SymbolF');
echo $context->resolve($symbol);               // outputs '\NamespaceD\SymbolB\SymbolE\SymbolF'
echo $symbol->resolveAgainstContext($context); // outputs '\NamespaceD\SymbolB\SymbolE\SymbolF'

$symbol = Symbol::fromString('namespace\SymbolA');
echo $context->resolve($symbol);               // outputs '\NamespaceA\NamespaceB\SymbolA'
echo $symbol->resolveAgainstContext($context); // outputs '\NamespaceA\NamespaceB\SymbolA'

$symbol = Symbol::fromString('namespace\..\SymbolA');
echo $context->resolve($symbol)->normalize();               // outputs '\NamespaceA\SymbolA'
echo $symbol->resolveAgainstContext($context)->normalize(); // outputs '\NamespaceA\SymbolA'

$symbol = Symbol::fromString('SymbolD');
echo $context->resolve($symbol);                                       // outputs '\NamespaceA\NamespaceB\SymbolD'
echo $symbol->resolveAgainstContext($context);                         // outputs '\NamespaceA\NamespaceB\SymbolD'
echo $context->resolve($symbol, SymbolType::FUNCT1ON());               // outputs '\NamespaceE\SymbolD' (assuming the function exists)
echo $symbol->resolveAgainstContext($context, SymbolType::FUNCT1ON()); // outputs '\NamespaceE\SymbolD' (assuming the function exists)
    }

    public function testContextReadMethods()
    {
        $path = __FILE__;
        $stream = fopen('php://memory', 'rb+');

ResolutionContext::fromObject($this);                                          // from an object instance
ResolutionContext::fromSymbol(__CLASS__);                                      // from a symbol
ResolutionContext::fromFunctionSymbol('NamespaceE\SymbolD');                   // from a function symbol
ResolutionContext::fromClass(new ReflectionClass(__CLASS__));                  // from a class reflector
ResolutionContext::fromClass(new ReflectionObject($this));                     // from an object reflector
ResolutionContext::fromFunction(new ReflectionFunction('NamespaceE\SymbolD')); // from a function reflector
ResolutionContext::fromFile($path);                                            // from the first context in a file
ResolutionContext::fromFileByIndex($path, 0);                                  // from the nth context in a file
ResolutionContext::fromFileByPosition($path, new ParserPosition(11, 22));      // from a line and column number in a file
ResolutionContext::fromStream($stream);                                        // from the first context in a stream
ResolutionContext::fromStreamByIndex($stream, 0);                              // from the nth context in a stream
ResolutionContext::fromStreamByPosition($stream, new ParserPosition(11, 22));  // from a line and column number in a stream

        fclose($stream);
        $this->assertTrue(true);
    }

    public function testFixedResolverExample()
    {
        $object = new ClassA;

        $this->expectOutputString('\NamespaceB\SymbolA');
        $object->methodA();
    }
}
