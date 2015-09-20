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

- Reading 'resolution contexts' (sets of [namespace] and/or [use] statements)
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

## Reading resolution contexts

*Cosmos* uses the term 'resolution context' to refer to a combination of
`namespace` and `use` statements against which a symbol can be resolved.

In the case of comment annotations, and other symbols defined in source code,
the resolution context must be parsed from the original source code in order to
resolve these symbols correctly.

There are many ways to read a resolution context. Note that internally, all of
these methods are parsing source code:

```php
use Eloquent\Cosmos\Persistence\ResolutionContextReader;

$reader = ResolutionContextReader::instance();

// from an object instance
$context = $reader->readFromObject($this);

// from a symbol
$context = $reader->readFromSymbol(__CLASS__);

// from a function symbol
$context = $reader->readFromFunctionSymbol(__FUNCTION__);

// from a class reflector
$context = $reader->readFromClass(new ReflectionClass(__CLASS__));

// from an object reflector
$context = $reader->readFromClass(new ReflectionObject($this));

// from a function reflector
$context = $reader->readFromFunction(new ReflectionFunction(__FUNCTION__));

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
```

## Manually creating resolution contexts

Resolution contexts can be created manually:

```php
use Eloquent\Cosmos\Resolution\Context\ResolutionContext;
use Eloquent\Cosmos\Symbol\Symbol;
use Eloquent\Cosmos\UseStatement\UseStatement;

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
```

This is equivalent to (but much faster than):

```php
use Eloquent\Cosmos\Persistence\ResolutionContextReader;

$reader = ResolutionContextReader::instance();

$context = $reader->readFromSource('<?php
    namespace NamespaceA\NamespaceB;

    use NamespaceC\ClassA;
    use NamespaceD\ClassA as ClassB;
    use function NamespaceE\functionA;
');
```

*Cosmos* also includes factories for the creation of symbols, use statements,
and resolution contexts for when a dependency injection approach is preferred.

## Resolving symbols

*Cosmos* allows symbols to be resolved at run time in the exact same way that
the PHP interpreter handles them at compile time:

```php
namespace NamespaceA\NamespaceB;

use Eloquent\Cosmos\Persistence\ResolutionContextReader;
use Eloquent\Cosmos\Resolution\FunctionSymbolResolver;
use Eloquent\Cosmos\Resolution\SymbolResolver;
use Eloquent\Cosmos\Symbol\Symbol;
use NamespaceC\SymbolA;
use NamespaceD\SymbolB as SymbolC;
use function NamespaceE\functionA;

$reader = ResolutionContextReader::instance();
$resolver = SymbolResolver::instance();

// read the first context in this file
$context = $reader->readFromFile(__FILE__);

$symbol = Symbol::fromString('SymbolA');
$resolved = $resolver->resolve($context, $symbol);
echo $resolved; // outputs '\NamespaceC\SymbolA'

$symbol = Symbol::fromString('SymbolC');
$resolved = $resolver->resolve($context, $symbol);
echo $resolved; // outputs '\NamespaceD\SymbolB'

$symbol = Symbol::fromString('SymbolX');
$resolved = $resolver->resolve($context, $symbol);
echo $resolved; // outputs '\NamespaceA\NamespaceB\SymbolX'

$symbol = Symbol::fromString('SymbolA\SymbolX\SymbolY');
$resolved = $resolver->resolve($context, $symbol);
echo $resolved; // outputs '\NamespaceC\SymbolA\SymbolX\SymbolY'

$symbol = Symbol::fromString('\SymbolA');
$resolved = $resolver->resolve($context, $symbol);
echo $resolved; // outputs '\SymbolA'

$symbol = Symbol::fromString('namespace\SymbolA'); // yes, this is a real thing
$resolved = $resolver->resolve($context, $symbol);
echo $resolved; // outputs '\NamespaceA\NamespaceB\SymbolA'

$resolver = FunctionSymbolResolver::instance();

$symbol = Symbol::fromString('functionA');
$resolved = $resolver->resolve($context, $symbol);
echo $resolved; // outputs '\NamespaceE\functionA' (assuming the function exists)
```

At first it may not be obvious that a library is required to perform this type
of resolution. It would be reasonable to assume that PHP's reflection API would
provide this functionality natively; however, this is sadly not the case.

## Generating an optimal resolution context

*Cosmos* can generate an optimal resolution context for a given set of symbols.
This can be useful for code generation, allowing the use of single-atom
references instead of fully qualified symbols throughout the generated source
code:

```php
use Eloquent\Cosmos\Resolution\Context\ResolutionContextGenerator;
use Eloquent\Cosmos\Symbol\Symbol;

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
```

The generated context is then equivalent to:

```php
namespace NamespaceA\NamespaceB;

use ClassD;
use NamespaceA\NamespaceB\NamespaceC\ClassB;
use NamespaceD\NamespaceE\ClassC as NamespaceEClassC;
use NamespaceD\NamespaceF\ClassC as NamespaceFClassC;
```

Notice that *Cosmos* has automatically accounted for symbols that are children
of the namespace, and avoided name collisions where necessary.

## Finding the shortest reference to a symbol

*Cosmos* can determine the shortest symbol reference that will resolve to a
given qualified symbol, relative to a context:

```php
use Eloquent\Cosmos\Persistence\ResolutionContextReader;
use Eloquent\Cosmos\Resolution\SymbolReferenceGenerator;
use Eloquent\Cosmos\Symbol\Symbol;

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

$symbol = Symbol::fromString('\NamespaceC\SymbolA');
$reference = $referenceGenerator->referenceTo($context, $symbol);
echo $reference; // outputs 'SymbolA'

$symbol = Symbol::fromString('\NamespaceD\SymbolB');
$reference = $referenceGenerator->referenceTo($context, $symbol);
echo $reference; // outputs 'SymbolC'

$symbol = Symbol::fromString('\NamespaceD\SymbolB\SymbolD');
$reference = $referenceGenerator->referenceTo($context, $symbol);
echo $reference; // outputs 'SymbolC\SymbolD'

$symbol = Symbol::fromString('\NamespaceA\NamespaceB\SymbolA');
$reference = $referenceGenerator->referenceTo($context, $symbol);
echo $reference; // outputs 'namespace\SymbolA'

$symbol = Symbol::fromString('\NamespaceA\NamespaceE\SymbolD');
$reference = $referenceGenerator->referenceTo($context, $symbol);
echo $reference; // outputs '\NamespaceA\NamespaceE\SymbolD'
```

## What is a symbol?

In *Cosmos*, a 'symbol' is a generic object used to represent class, interface,
trait, namespace, function, and constant names. There are two primary types of
symbols: 'qualified symbols', and 'symbol references'.

*Qualified symbols* are similar to an absolute path in a file system. That is,
they contain all the information necessary to uniquely identify the class,
function, or other entity to which they refer. The following are examples of
qualified symbols:

- `\ArrayObject`
- `\ReflectionClass`
- `\Psr\Log\LoggerInterface`

*Symbol references* are similar to a relative path in a file system. They are a
kind of 'pointer' to an entity. Which entity they point to depends on the
context. When combined with a `namespace` statement, and/or a set of `use`
statements, a symbol reference can be resolved into a qualified symbol. The
following are examples of symbol references:

- `ArrayObject`
- `ReflectionClass`
- `Psr\Log\LoggerInterface`
- `namespace\NamespaceA\ClassA`

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
