<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution;

use Eloquent\Cosmos\Exception\ReadException;
use Eloquent\Cosmos\Exception\UndefinedSymbolException;
use Eloquent\Cosmos\Resolution\Context\Factory\Exception\UndefinedResolutionContextException;
use Eloquent\Cosmos\Resolution\Context\Parser\ParserPositionInterface;
use Eloquent\Cosmos\Resolution\Context\ResolutionContext;
use Eloquent\Cosmos\Resolution\Context\ResolutionContextInterface;
use Eloquent\Cosmos\Resolution\Factory\FixedContextSymbolResolverFactory;
use Eloquent\Cosmos\Resolution\Factory\FixedContextSymbolResolverFactoryInterface;
use Eloquent\Cosmos\Symbol\QualifiedSymbolInterface;
use Eloquent\Cosmos\Symbol\SymbolInterface;
use Eloquent\Pathogen\FileSystem\FileSystemPathInterface;
use Eloquent\Pathogen\PathInterface;
use Eloquent\Pathogen\Resolver\PathResolverInterface;
use ReflectionClass;
use ReflectionFunction;

/**
 * Resolves symbols against a fixed context.
 */
class FixedContextSymbolResolver implements PathResolverInterface
{
    /**
     * Create a new fixed context symbol resolver for the supplied object.
     *
     * @param object $object The object.
     *
     * @return PathResolverInterface The newly created resolver.
     * @throws ReadException         If the source code cannot be read.
     */
    public static function fromObject($object)
    {
        return static::factory()->createFromObject($object);
    }

    /**
     * Create a new fixed context symbol resolver for the supplied class,
     * interface, or trait symbol.
     *
     * @param SymbolInterface|string $symbol The symbol.
     *
     * @return PathResolverInterface    The newly created resolver.
     * @throws ReadException            If the source code cannot be read.
     * @throws UndefinedSymbolException If the symbol does not exist, or cannot be found in the source code.
     */
    public static function fromSymbol($symbol)
    {
        return static::factory()->createFromSymbol($symbol);
    }

    /**
     * Create a new fixed context symbol resolver for the supplied function
     * symbol.
     *
     * @param SymbolInterface|string $symbol The symbol.
     *
     * @return PathResolverInterface    The newly created resolver.
     * @throws ReadException            If the source code cannot be read.
     * @throws UndefinedSymbolException If the symbol does not exist, or cannot be found in the source code.
     */
    public static function fromFunctionSymbol($symbol)
    {
        return static::factory()->createFromFunctionSymbol($symbol);
    }

    /**
     * Create a new fixed context symbol resolver for the supplied class or
     * object reflector.
     *
     * @param ReflectionClass $class The class or object reflector.
     *
     * @return PathResolverInterface    The newly created resolver.
     * @throws ReadException            If the source code cannot be read.
     * @throws UndefinedSymbolException If the symbol cannot be found in the source code.
     */
    public static function fromClass(ReflectionClass $class)
    {
        return static::factory()->createFromClass($class);
    }

    /**
     * Create a new fixed context symbol resolver for the supplied function
     * reflector.
     *
     * @param ReflectionFunction $function The function reflector.
     *
     * @return PathResolverInterface    The newly created resolver.
     * @throws ReadException            If the source code cannot be read.
     * @throws UndefinedSymbolException If the symbol cannot be found in the source code.
     */
    public static function fromFunction(ReflectionFunction $function)
    {
        return static::factory()->createFromFunction($function);
    }

    /**
     * Create a new fixed context symbol resolver for the first context found in
     * a file.
     *
     * @param FileSystemPathInterface|string $path The path.
     *
     * @return PathResolverInterface The newly created resolver.
     * @throws ReadException         If the source code cannot be read.
     */
    public static function fromFile($path)
    {
        return static::factory()->createFromFile($path);
    }

    /**
     * Create a new fixed context symbol resolver for the context found at the
     * specified index in a file.
     *
     * @param FileSystemPathInterface|string $path  The path.
     * @param integer                        $index The index.
     *
     * @return ResolutionContextInterface          The newly created resolution context.
     * @throws ReadException                       If the source code cannot be read.
     * @throws UndefinedResolutionContextException If there is no resolution context at the specified index.
     */
    public static function fromFileByIndex($path, $index)
    {
        return static::factory()->createFromFileByIndex($path, $index);
    }

    /**
     * Create a new fixed context symbol resolver for the context found at the
     * specified position in a file.
     *
     * @param FileSystemPathInterface|string $path     The path.
     * @param ParserPositionInterface        $position The position.
     *
     * @return PathResolverInterface The newly created resolver.
     * @throws ReadException         If the source code cannot be read.
     */
    public static function fromFileByPosition(
        $path,
        ParserPositionInterface $position
    ) {
        return static::factory()->createFromFileByPosition($path, $position);
    }

    /**
     * Create a new fixed context symbol resolver for the first context found in
     * a stream.
     *
     * @param stream                              $stream The stream.
     * @param FileSystemPathInterface|string|null $path   The path, if known.
     *
     * @return PathResolverInterface The newly created resolver.
     * @throws ReadException         If the source code cannot be read.
     */
    public static function fromStream($stream, $path = null)
    {
        return static::factory()->createFromStream($stream, $path);
    }

    /**
     * Create a new fixed context symbol resolver for the context found at the
     * specified index in a stream.
     *
     * @param stream                              $stream The stream.
     * @param integer                             $index  The index.
     * @param FileSystemPathInterface|string|null $path   The path, if known.
     *
     * @return ResolutionContextInterface          The newly created resolution context.
     * @throws ReadException                       If the source code cannot be read.
     * @throws UndefinedResolutionContextException If there is no resolution context at the specified index.
     */
    public static function fromStreamByIndex($stream, $index, $path = null)
    {
        return static::factory()
            ->createFromStreamByIndex($stream, $index, $path);
    }

    /**
     * Create a new fixed context symbol resolver for the context found at the
     * specified position in a stream.
     *
     * @param stream                              $stream   The stream.
     * @param ParserPositionInterface             $position The position.
     * @param FileSystemPathInterface|string|null $path     The path, if known.
     *
     * @return PathResolverInterface The newly created resolver.
     * @throws ReadException         If the source code cannot be read.
     */
    public static function fromStreamByPosition(
        $stream,
        ParserPositionInterface $position,
        $path = null
    ) {
        return static::factory()
            ->createFromStreamByPosition($stream, $position, $path);
    }

    /**
     * Construct a new fixed context symbol resolver.
     *
     * @param ResolutionContextInterface|null $context  The resolution context.
     * @param SymbolResolverInterface|null    $resolver The internal symbol resolver to use.
     */
    public function __construct(
        ResolutionContextInterface $context = null,
        SymbolResolverInterface $resolver = null
    ) {
        if (null === $context) {
            $context = new ResolutionContext;
        }
        if (null === $resolver) {
            $resolver = SymbolResolver::instance();
        }

        $this->context = $context;
        $this->resolver = $resolver;
    }

    /**
     * Get the resolution context.
     *
     * @return ResolutionContextInterface The resolution context.
     */
    public function context()
    {
        return $this->context;
    }

    /**
     * Get the internal resolver.
     *
     * @return SymbolResolverInterface The internal resolver.
     */
    public function resolver()
    {
        return $this->resolver;
    }

    /**
     * Resolve a symbol against a fixed context.
     *
     * Symbols that are already qualified will be returned unaltered.
     *
     * @param PathInterface $symbol The symbol to resolve.
     *
     * @return QualifiedSymbolInterface The resolved, qualified symbol.
     */
    public function resolve(PathInterface $symbol)
    {
        return $this->resolver()
            ->resolveAgainstContext($this->context(), $symbol);
    }

    /**
     * Get the fixed context symbol resolver factory.
     *
     * @return FixedContextSymbolResolverFactoryInterface The fixed context symbol resolver factory.
     */
    protected static function factory()
    {
        return FixedContextSymbolResolverFactory::instance();
    }

    private $context;
    private $resolver;
}
