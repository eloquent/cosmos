<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Factory;

use Eloquent\Cosmos\Exception\ReadException;
use Eloquent\Cosmos\Exception\UndefinedSymbolException;
use Eloquent\Cosmos\Resolution\Context\Factory\Exception\UndefinedResolutionContextException;
use Eloquent\Cosmos\Resolution\Context\Parser\ParserPositionInterface;
use Eloquent\Cosmos\Resolution\Context\ResolutionContextInterface;
use Eloquent\Cosmos\Symbol\SymbolInterface;
use Eloquent\Pathogen\Resolver\PathResolverInterface;
use ReflectionClass;
use ReflectionFunction;

/**
 * The interface implemented by fixed context symbol resolver factories.
 */
interface FixedContextSymbolResolverFactoryInterface
{
    /**
     * Construct a new fixed context symbol resolver.
     *
     * @param ResolutionContextInterface|null $context The resolution context.
     *
     * @return PathResolverInterface The newly created resolver.
     */
    public function create(ResolutionContextInterface $context = null);

    /**
     * Create a new fixed context symbol resolver for the supplied object.
     *
     * @param object $object The object.
     *
     * @return PathResolverInterface The newly created resolver.
     * @throws ReadException         If the source code cannot be read.
     */
    public function createFromObject($object);

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
    public function createFromSymbol($symbol);

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
    public function createFromFunctionSymbol($symbol);

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
    public function createFromClass(ReflectionClass $class);

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
    public function createFromFunction(ReflectionFunction $function);

    /**
     * Create a new fixed context symbol resolver for the first context found in
     * a file.
     *
     * @param string $path The path.
     *
     * @return PathResolverInterface The newly created resolver.
     * @throws ReadException         If the source code cannot be read.
     */
    public function createFromFile($path);

    /**
     * Create a new fixed context symbol resolver for the context found at the
     * specified index in a file.
     *
     * @param string  $path  The path.
     * @param integer $index The index.
     *
     * @return ResolutionContextInterface          The newly created resolution context.
     * @throws ReadException                       If the source code cannot be read.
     * @throws UndefinedResolutionContextException If there is no resolution context at the specified index.
     */
    public function createFromFileByIndex($path, $index);

    /**
     * Create a new fixed context symbol resolver for the context found at the
     * specified position in a file.
     *
     * @param string                  $path     The path.
     * @param ParserPositionInterface $position The position.
     *
     * @return PathResolverInterface The newly created resolver.
     * @throws ReadException         If the source code cannot be read.
     */
    public function createFromFileByPosition(
        $path,
        ParserPositionInterface $position
    );

    /**
     * Create a new fixed context symbol resolver for the first context found in
     * a stream.
     *
     * @param stream      $stream The stream.
     * @param string|null $path   The path, if known.
     *
     * @return PathResolverInterface The newly created resolver.
     * @throws ReadException         If the source code cannot be read.
     */
    public function createFromStream($stream, $path = null);

    /**
     * Create a new fixed context symbol resolver for the context found at the
     * specified index in a stream.
     *
     * @param stream      $stream The stream.
     * @param integer     $index  The index.
     * @param string|null $path   The path, if known.
     *
     * @return ResolutionContextInterface          The newly created resolution context.
     * @throws ReadException                       If the source code cannot be read.
     * @throws UndefinedResolutionContextException If there is no resolution context at the specified index.
     */
    public function createFromStreamByIndex($stream, $index, $path = null);

    /**
     * Create a new fixed context symbol resolver for the context found at the
     * specified position in a stream.
     *
     * @param stream                  $stream   The stream.
     * @param ParserPositionInterface $position The position.
     * @param string|null             $path     The path, if known.
     *
     * @return PathResolverInterface The newly created resolver.
     * @throws ReadException         If the source code cannot be read.
     */
    public function createFromStreamByPosition(
        $stream,
        ParserPositionInterface $position,
        $path = null
    );
}
