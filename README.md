# Cosmos

*A library for representing and manipulating PHP symbols.*

[![The most recent stable version is 2.3.1][version-image]][semantic versioning]
[![Current build status image][build-image]][build status]
[![Current test coverage image][coverage-image]][test coverage]

[build-image]: https://img.shields.io/travis/eloquent/cosmos/master.svg?style=flat-square "Build status"
[coverage-image]: https://img.shields.io/codecov/c/github/eloquent/cosmos/master.svg?style=flat-square "Test coverage"
[build status]: https://travis-ci.org/eloquent/cosmos
[test coverage]: https://codecov.io/github/eloquent/cosmos
[semantic versioning]: http://semver.org/
[version-image]: http://img.shields.io/:semver-2.3.1-brightgreen.svg?style=flat-square "This project uses semantic versioning"

## Installation and documentation

- Available as [Composer] package [eloquent/cosmos].
- [API documentation] available.

[api documentation]: http://lqnt.co/cosmos/artifacts/documentation/api/
[composer]: http://getcomposer.org/
[eloquent/cosmos]: https://packagist.org/packages/eloquent/cosmos

## What is *Cosmos*?

*Cosmos* is a library for representing and manipulating PHP symbols. Supported
symbol types include class, interface, trait, namespace, function, and constant
names. *Cosmos* is designed for:

- Reading *resolution contexts* (sets of [namespace] and/or [use] statements)
  from source code.
- Resolving symbols relative to a resolution context.
- Finding the shortest reference to a symbol relative to a resolution context.
- Generating an optimal resolution context for a set of symbols.

*Cosmos* is primarily designed to resolve symbols contained in comment
annotations, and as a tool to assist in code generation.

*Cosmos* allows the handling of symbols at run time in the exact same way that
the PHP interpreter handles them at compile time. To this end, it supports
modern PHP features including `use function` and `use const`, as well as many
edge-case scenarios, such as the use of the `namespace` keyword as a symbol
prefix.

[namespace]: http://php.net/manual/en/language.namespaces.definition.php
[use]: http://php.net/manual/en/language.namespaces.importing.php

## Resolving a symbol

Symbols can be resolved against a full set of `namespace` and `use` statements.
In this case the statements are defined manually, but they can also be read from
existing source code:

```php
use Eloquent\Cosmos\Resolution\Context\ResolutionContext;
use Eloquent\Cosmos\Symbol\Symbol;
use Eloquent\Cosmos\Symbol\SymbolType;
use Eloquent\Cosmos\UseStatement\UseStatement;
use Eloquent\Cosmos\UseStatement\UseStatementType;

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
echo $symbol->resolveAgainstContext($context); // outputs '\SymbolA'

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
echo $context->resolve($symbol);               // outputs '\NamespaceA\SymbolA'
echo $symbol->resolveAgainstContext($context); // outputs '\NamespaceA\SymbolA'

$symbol = Symbol::fromString('SymbolD');
echo $context->resolve($symbol);                                       // outputs '\NamespaceA\NamespaceB\SymbolD'
echo $symbol->resolveAgainstContext($context);                         // outputs '\NamespaceA\NamespaceB\SymbolD'
echo $context->resolve($symbol, SymbolType::FUNCT1ON());               // outputs '\NamespaceE\SymbolD' (assuming the function exists)
echo $symbol->resolveAgainstContext($context, SymbolType::FUNCT1ON()); // outputs '\NamespaceE\SymbolD' (assuming the function exists)
```

## Reading a resolution context

There are many ways to read an existing symbol resolution context. Note that all
of these methods involve actually parsing source code to determine the relevant
`namespace` and `use` statements:

```php
use Eloquent\Cosmos\Resolution\Context\Parser\ParserPosition;
use Eloquent\Cosmos\Resolution\Context\ResolutionContext;

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
```

There are also identical factory methods for 'fixed' symbol resolvers, which
make the entire process of reading and resolution very streamlined. For example,
in the middle of a class, getting a resolver for the current context is as
simple as:

```php
namespace NamespaceA;

use Eloquent\Cosmos\Resolution\FixedContextSymbolResolver;
use Eloquent\Cosmos\Symbol\Symbol;
use NamespaceB\SymbolA as SymbolB;
use SymbolC;

class ClassA
{
    public function methodA()
    {
        $resolver = FixedContextSymbolResolver::fromObject($this);

        echo $resolver->resolve(Symbol::fromString('SymbolB')); // outputs '\NamespaceB\SymbolA'
    }
}
```

## Finding the shortest reference to a symbol

*Cosmos* can determine the shortest symbol reference that will resolve to a
given qualified symbol relative to a context:

```php
use Eloquent\Cosmos\Resolution\Context\ResolutionContext;
use Eloquent\Cosmos\Symbol\Symbol;
use Eloquent\Cosmos\UseStatement\UseStatement;

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
    )
);

$symbol = Symbol::fromString('\NamespaceA\NamespaceB\SymbolD');
echo $symbol->relativeToContext($context); // outputs 'SymbolD'

$symbol = Symbol::fromString('\NamespaceC\SymbolA');
echo $symbol->relativeToContext($context); // outputs 'SymbolA'

$symbol = Symbol::fromString('\NamespaceD\SymbolB');
echo $symbol->relativeToContext($context); // outputs 'SymbolC'

$symbol = Symbol::fromString('\NamespaceD\SymbolB\SymbolD');
echo $symbol->relativeToContext($context); // outputs 'SymbolB\SymbolD'

$symbol = Symbol::fromString('\NamespaceA\NamespaceB\SymbolA');
echo $symbol->relativeToContext($context); // outputs 'namespace\SymbolA'

$symbol = Symbol::fromString('\NamespaceA\NamespaceE\SymbolD');
echo $symbol->relativeToContext($context); // outputs '\NamespaceA\NamespaceE\SymbolD'
```

## Generating an optimal resolution context

*Cosmos* can generate an optimal resolution context for a given set of symbols
to be used:

```php
use Eloquent\Cosmos\Resolution\Context\Generator\ResolutionContextGenerator;
use Eloquent\Cosmos\Symbol\Symbol;

$generator = new ResolutionContextGenerator;
$context = $generator->generate(
    Symbol::fromString('\NamespaceA\NamespaceB'),
    array(
        Symbol::fromString('\NamespaceA\NamespaceB\ClassA'),
        Symbol::fromString('\NamespaceA\NamespaceB\NamespaceC\ClassB'),
        Symbol::fromString('\NamespaceD\NamespaceE\ClassC'),
        Symbol::fromString('\NamespaceD\NamespaceF\ClassC'),
        Symbol::fromString('\ClassD'),
    )
);
```

The generated context is then equivalent to:

```php
namespace NamespaceA\NamespaceB;

use ClassD;
use NamespaceA\NamespaceB\NamespaceC\ClassB;
use NamespaceD\NamespaceE\ClassC as NamespaceEClassC;
use NamespaceD\NamespaceF\ClassC as NamespaceFClassC;
```

## What is a symbol?

In *Cosmos*, a *symbol* is a generic object used to represent class, interface,
trait, namespace, function, and constant names. There are two primary types of
symbols: *qualified symbols*, and *symbol references*.

*Qualified symbols* are similar to an absolute path in a file system. That is,
they contain all the information necessary to uniquely identify the class,
function, or other entity to which they refer. The following are examples of
qualified symbols:

- `\ArrayObject`
- `\ReflectionClass`
- `\Psr\Log\LoggerInterface`

*Symbol references* are similar to a relative path in a file system. They are a
kind of 'pointer' to an entity. Which entity they point to depends on the
context. When combined with a current `namespace`, and/or a set of `use`
statements, a symbol reference can be resolved into a qualified symbol. The
following are examples of symbol references:

- `ArrayObject`
- `ReflectionClass`
- `Psr\Log\LoggerInterface`
- `namespace\SubNamespace\ClassA`

Note that *Cosmos* uses different terminology to the PHP manual, especially with
regards to the term 'qualified'. In the PHP manual, any symbol with more than
one atom is regarded as 'qualified', and a symbol starting with a namespace
separator is regarded as 'fully qualified'. In contrast, *Cosmos* refers to any
symbol starting with a namespace separator as 'qualified', and anything else as
a 'reference'.

## Symbol atoms

Symbols in *Cosmos* are comprised of 'atoms'. Atoms are the individual portions
of the symbol separated by the namespace separator (`\`). For example, the
symbol `Psr\Log\LoggerInterface` has atoms `Psr`, `Log`, and `LoggerInterface`.
