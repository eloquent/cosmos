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

use Eloquent\Cosmos\Exception\ReadException;
use Eloquent\Cosmos\Exception\UndefinedSymbolException;
use Eloquent\Cosmos\Resolution\Context\Parser\ParserPositionInterface;
use Eloquent\Cosmos\Resolution\Context\Persistence\Exception\UndefinedResolutionContextException;
use Eloquent\Cosmos\Resolution\Context\ResolutionContextInterface;
use Eloquent\Cosmos\Symbol\SymbolInterface;
use Eloquent\Pathogen\FileSystem\FileSystemPathInterface;
use ReflectionClass;
use ReflectionFunction;

/**
 * The interface implemented by symbol resolution context readers.
 */
interface ResolutionContextReaderInterface
{
    /**
     * Create a new symbol resolution context for the supplied object.
     *
     * @param object $object The object.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws ReadException              If the source code cannot be read.
     */
    public function readFromObject($object);

    /**
     * Create a new symbol resolution context for the supplied class, interface,
     * or trait symbol.
     *
     * @param SymbolInterface|string $symbol The symbol.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws ReadException              If the source code cannot be read.
     * @throws UndefinedSymbolException   If the symbol does not exist, or cannot be found in the source code.
     */
    public function readFromSymbol($symbol);

    /**
     * Create a new symbol resolution context for the supplied function symbol.
     *
     * @param SymbolInterface|string $symbol The symbol.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws ReadException              If the source code cannot be read.
     * @throws UndefinedSymbolException   If the symbol does not exist, or cannot be found in the source code.
     */
    public function readFromFunctionSymbol($symbol);

    /**
     * Create a new symbol resolution context for the supplied class or object
     * reflector.
     *
     * @param ReflectionClass $class The class or object reflector.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws ReadException              If the source code cannot be read.
     * @throws UndefinedSymbolException   If the symbol cannot be found in the source code.
     */
    public function readFromClass(ReflectionClass $class);

    /**
     * Create a new symbol resolution context for the supplied function
     * reflector.
     *
     * @param ReflectionFunction $function The function reflector.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws ReadException              If the source code cannot be read.
     * @throws UndefinedSymbolException   If the symbol cannot be found in the source code.
     */
    public function readFromFunction(ReflectionFunction $function);

    /**
     * Create the first context found in a file.
     *
     * @param FileSystemPathInterface|string $path The path.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws ReadException              If the source code cannot be read.
     */
    public function readFromFile($path);

    /**
     * Create the context found at the specified index in a file.
     *
     * @param FileSystemPathInterface|string $path  The path.
     * @param integer                        $index The index.
     *
     * @return ResolutionContextInterface          The newly created resolution context.
     * @throws ReadException                       If the source code cannot be read.
     * @throws UndefinedResolutionContextException If there is no resolution context at the specified index.
     */
    public function readFromFileByIndex($path, $index);

    /**
     * Create the context found at the specified position in a file.
     *
     * @param FileSystemPathInterface|string $path     The path.
     * @param ParserPositionInterface        $position The position.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws ReadException              If the source code cannot be read.
     */
    public function readFromFileByPosition(
        $path,
        ParserPositionInterface $position
    );

    /**
     * Create the first context found in a stream.
     *
     * @param stream                              $stream The stream.
     * @param FileSystemPathInterface|string|null $path   The path, if known.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws ReadException              If the source code cannot be read.
     */
    public function readFromStream($stream, $path = null);

    /**
     * Create the context found at the specified index in a stream.
     *
     * @param stream                              $stream The stream.
     * @param integer                             $index  The index.
     * @param FileSystemPathInterface|string|null $path   The path, if known.
     *
     * @return ResolutionContextInterface          The newly created resolution context.
     * @throws ReadException                       If the source code cannot be read.
     * @throws UndefinedResolutionContextException If there is no resolution context at the specified index.
     */
    public function readFromStreamByIndex($stream, $index, $path = null);

    /**
     * Create the context found at the specified position in a stream.
     *
     * @param stream                              $stream   The stream.
     * @param ParserPositionInterface             $position The position.
     * @param FileSystemPathInterface|string|null $path     The path, if known.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws ReadException              If the source code cannot be read.
     */
    public function readFromStreamByPosition(
        $stream,
        ParserPositionInterface $position,
        $path = null
    );
}
