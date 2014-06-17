<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Context;

use Eloquent\Cosmos\Exception\ReadException;
use Eloquent\Cosmos\Exception\UndefinedSymbolException;
use Eloquent\Cosmos\Resolution\Context\Factory\Exception\UndefinedResolutionContextException;
use Eloquent\Cosmos\Resolution\Context\Factory\ResolutionContextFactory;
use Eloquent\Cosmos\Resolution\Context\Factory\ResolutionContextFactoryInterface;
use Eloquent\Cosmos\Resolution\Context\Parser\ParserPositionInterface;
use Eloquent\Cosmos\Symbol\Factory\SymbolFactory;
use Eloquent\Cosmos\Symbol\Factory\SymbolFactoryInterface;
use Eloquent\Cosmos\Symbol\QualifiedSymbolInterface;
use Eloquent\Cosmos\Symbol\SymbolInterface;
use Eloquent\Cosmos\Symbol\SymbolReferenceInterface;
use Eloquent\Cosmos\UseStatement\UseStatementInterface;
use Eloquent\Pathogen\FileSystem\FileSystemPathInterface;
use ReflectionClass;
use ReflectionFunction;

/**
 * Represents a combined namespace and set of use statements.
 */
class ResolutionContext implements ResolutionContextInterface
{
    /**
     * Create a new symbol resolution context for the supplied object.
     *
     * @param object $object The object.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws ReadException              If the source code cannot be read.
     */
    public static function fromObject($object)
    {
        return static::factory()->createFromObject($object);
    }

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
    public static function fromSymbol($symbol)
    {
        return static::factory()->createFromSymbol($symbol);
    }

    /**
     * Create a new symbol resolution context for the supplied function symbol.
     *
     * @param SymbolInterface|string $symbol The symbol.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws ReadException              If the source code cannot be read.
     * @throws UndefinedSymbolException   If the symbol does not exist, or cannot be found in the source code.
     */
    public static function fromFunctionSymbol($symbol)
    {
        return static::factory()->createFromFunctionSymbol($symbol);
    }

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
    public static function fromClass(ReflectionClass $class)
    {
        return static::factory()->createFromClass($class);
    }

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
    public static function fromFunction(ReflectionFunction $function)
    {
        return static::factory()->createFromFunction($function);
    }

    /**
     * Create the first context found in a file.
     *
     * @param FileSystemPathInterface|string $path The path.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws ReadException              If the source code cannot be read.
     */
    public static function fromFile($path)
    {
        return static::factory()->createFromFile($path);
    }

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
    public static function fromFileByIndex($path, $index)
    {
        return static::factory()->createFromFileByIndex($path, $index);
    }

    /**
     * Create the context found at the specified position in a file.
     *
     * @param FileSystemPathInterface|string $path     The path.
     * @param ParserPositionInterface        $position The position.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws ReadException              If the source code cannot be read.
     */
    public static function fromFileByPosition(
        $path,
        ParserPositionInterface $position
    ) {
        return static::factory()->createFromFileByPosition($path, $position);
    }

    /**
     * Create the first context found in a stream.
     *
     * @param stream                              $stream The stream.
     * @param FileSystemPathInterface|string|null $path   The path, if known.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws ReadException              If the source code cannot be read.
     */
    public static function fromStream($stream, $path = null)
    {
        return static::factory()->createFromStream($stream, $path);
    }

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
    public static function fromStreamByIndex($stream, $index, $path = null)
    {
        return static::factory()->createFromStreamByIndex($stream, $index, $path);
    }

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
    public static function fromStreamByPosition(
        $stream,
        ParserPositionInterface $position,
        $path = null
    ) {
        return static::factory()
            ->createFromStreamByPosition($stream, $position, $path);
    }

    /**
     * Construct a new symbol resolution context.
     *
     * @param QualifiedSymbolInterface|null     $primaryNamespace The namespace.
     * @param array<UseStatementInterface>|null $useStatements    The use statements.
     * @param SymbolFactoryInterface|null       $symbolFactory    The symbol factory to use.
     */
    public function __construct(
        QualifiedSymbolInterface $primaryNamespace = null,
        array $useStatements = null,
        SymbolFactoryInterface $symbolFactory = null
    ) {
        if (null === $symbolFactory) {
            $symbolFactory = SymbolFactory::instance();
        }
        if (null === $primaryNamespace) {
            $primaryNamespace = $symbolFactory->globalNamespace();
        }
        if (null === $useStatements) {
            $useStatements = array();
        }

        $this->primaryNamespace = $primaryNamespace;
        $this->useStatements = $useStatements;

        $this->index = $this->buildIndex();
    }

    /**
     * Get the namespace.
     *
     * @return QualifiedSymbolInterface The namespace.
     */
    public function primaryNamespace()
    {
        return $this->primaryNamespace;
    }

    /**
     * Get the use statements.
     *
     * @return array<UseStatementInterface> The use statements.
     */
    public function useStatements()
    {
        return $this->useStatements;
    }

    /**
     * Get the symbol associated with the supplied symbol reference's first
     * atom.
     *
     * @param SymbolReferenceInterface $symbol The symbol reference.
     *
     * @return QualifiedSymbolInterface|null The symbol, or null if no associated symbol exists.
     */
    public function symbolByFirstAtom(SymbolReferenceInterface $symbol)
    {
        $index = $this->index();
        $firstAtom = $symbol->atomAt(0);
        if (array_key_exists($firstAtom, $index)) {
            return $index[$firstAtom];
        }

        return null;
    }

    /**
     * Accept a visitor.
     *
     * @param ResolutionContextVisitorInterface $visitor The visitor to accept.
     *
     * @return mixed The result of visitation.
     */
    public function accept(ResolutionContextVisitorInterface $visitor)
    {
        return $visitor->visitResolutionContext($this);
    }

    /**
     * Get the resolution context factory.
     *
     * @return ResolutionContextFactoryInterface The resolution context factory.
     */
    protected static function factory()
    {
        return ResolutionContextFactory::instance();
    }

    /**
     * Get an index for resolving symbol references.
     *
     * The first time this method is called, the index will be built.
     *
     * @return array The index.
     */
    protected function index()
    {
        return $this->index;
    }

    /**
     * Builds the internal index used to resolve symbol references.
     *
     * @return array The index.
     */
    protected function buildIndex()
    {
        $index = array();
        foreach ($this->useStatements() as $useStatement) {
            $index[$useStatement->effectiveAlias()->string()] =
                $useStatement->symbol();
        }

        return $index;
    }

    private $primaryNamespace;
    private $useStatements;
    private $index;
}
