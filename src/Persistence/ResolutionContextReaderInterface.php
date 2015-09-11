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

use Eloquent\Cosmos\Exception\ReadException;
use Eloquent\Cosmos\Exception\UndefinedResolutionContextException;
use Eloquent\Cosmos\Exception\UndefinedSymbolException;
use Eloquent\Cosmos\Resolution\Context\ResolutionContextInterface;
use Eloquent\Cosmos\Symbol\SymbolInterface;
use ReflectionClass;
use ReflectionFunction;

/**
 * The interface implemented by symbol resolution context readers.
 *
 * @api
 */
interface ResolutionContextReaderInterface
{
    /**
     * Create a new symbol resolution context for the supplied object.
     *
     * @api
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
     * @api
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
     * @api
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
     * @api
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
     * @api
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
     * @api
     *
     * @param string $path The path.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws ReadException              If the source code cannot be read.
     */
    public function readFromFile($path);

    /**
     * Create the context found at the specified index in a file.
     *
     * @api
     *
     * @param string  $path  The path.
     * @param integer $index The index.
     *
     * @return ResolutionContextInterface          The newly created resolution context.
     * @throws ReadException                       If the source code cannot be read.
     * @throws UndefinedResolutionContextException If there is no resolution context at the specified index.
     */
    public function readFromFileByIndex($path, $index);

    /**
     * Create the context found at the specified position in a file.
     *
     * @api
     *
     * @param string  $path   The path.
     * @param integer $line   The line.
     * @param integer $column The column.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws ReadException              If the source code cannot be read.
     */
    public function readFromFileByPosition($path, $line, $column = 1);

    /**
     * Create the first context found in a stream.
     *
     * @api
     *
     * @param stream      $stream The stream.
     * @param string|null $path   The path, if known.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws ReadException              If the source code cannot be read.
     */
    public function readFromStream($stream, $path = null);

    /**
     * Create the context found at the specified index in a stream.
     *
     * @api
     *
     * @param stream      $stream The stream.
     * @param integer     $index  The index.
     * @param string|null $path   The path, if known.
     *
     * @return ResolutionContextInterface          The newly created resolution context.
     * @throws ReadException                       If the source code cannot be read.
     * @throws UndefinedResolutionContextException If there is no resolution context at the specified index.
     */
    public function readFromStreamByIndex($stream, $index, $path = null);

    /**
     * Create the context found at the specified position in a stream.
     *
     * @api
     *
     * @param stream      $stream The stream.
     * @param integer     $line   The line.
     * @param integer     $column The column.
     * @param string|null $path   The path, if known.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws ReadException              If the source code cannot be read.
     */
    public function readFromStreamByPosition(
        $stream,
        $line,
        $column = 1,
        $path = null
    );

    /**
     * Create the first context found in the supplied source code.
     *
     * @param string $source The source code.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     */
    public function readFromSource($source);

    /**
     * Create the context found at the specified index in the supplied source
     * code.
     *
     * @param string  $source The source code.
     * @param integer $index  The index.
     *
     * @return ResolutionContextInterface          The newly created resolution context.
     * @throws UndefinedResolutionContextException If there is no resolution context at the specified index.
     */
    public function readFromSourceByIndex($source, $index);

    /**
     * Create the context found at the specified position in the supplied source
     * code.
     *
     * @api
     *
     * @param string  $source The source code.
     * @param integer $line   The line.
     * @param integer $column The column.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     */
    public function readFromSourceByPosition($source, $line, $column = 1);
}
