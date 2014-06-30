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
use Eloquent\Cosmos\Resolution\Context\Parser\ParserPositionInterface;
use Eloquent\Cosmos\Resolution\Context\Persistence\ResolutionContextReader;
use Eloquent\Cosmos\Resolution\Context\Persistence\ResolutionContextReaderInterface;
use Eloquent\Cosmos\Symbol\Factory\SymbolFactory;
use Eloquent\Cosmos\Symbol\Factory\SymbolFactoryInterface;
use Eloquent\Cosmos\Symbol\QualifiedSymbolInterface;
use Eloquent\Cosmos\Symbol\SymbolInterface;
use Eloquent\Cosmos\Symbol\SymbolReferenceInterface;
use Eloquent\Cosmos\Symbol\SymbolType;
use Eloquent\Cosmos\UseStatement\UseStatementInterface;
use Eloquent\Cosmos\UseStatement\UseStatementType;
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
        return static::reader()->readFromObject($object);
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
        return static::reader()->readFromSymbol($symbol);
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
        return static::reader()->readFromFunctionSymbol($symbol);
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
        return static::reader()->readFromClass($class);
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
        return static::reader()->readFromFunction($function);
    }

    /**
     * Create the first context found in a file.
     *
     * @param string $path The path.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws ReadException              If the source code cannot be read.
     */
    public static function fromFile($path)
    {
        return static::reader()->readFromFile($path);
    }

    /**
     * Create the context found at the specified index in a file.
     *
     * @param string  $path  The path.
     * @param integer $index The index.
     *
     * @return ResolutionContextInterface          The newly created resolution context.
     * @throws ReadException                       If the source code cannot be read.
     * @throws UndefinedResolutionContextException If there is no resolution context at the specified index.
     */
    public static function fromFileByIndex($path, $index)
    {
        return static::reader()->readFromFileByIndex($path, $index);
    }

    /**
     * Create the context found at the specified position in a file.
     *
     * @param string                  $path     The path.
     * @param ParserPositionInterface $position The position.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws ReadException              If the source code cannot be read.
     */
    public static function fromFileByPosition(
        $path,
        ParserPositionInterface $position
    ) {
        return static::reader()->readFromFileByPosition($path, $position);
    }

    /**
     * Create the first context found in a stream.
     *
     * @param stream      $stream The stream.
     * @param string|null $path   The path, if known.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws ReadException              If the source code cannot be read.
     */
    public static function fromStream($stream, $path = null)
    {
        return static::reader()->readFromStream($stream, $path);
    }

    /**
     * Create the context found at the specified index in a stream.
     *
     * @param stream      $stream The stream.
     * @param integer     $index  The index.
     * @param string|null $path   The path, if known.
     *
     * @return ResolutionContextInterface          The newly created resolution context.
     * @throws ReadException                       If the source code cannot be read.
     * @throws UndefinedResolutionContextException If there is no resolution context at the specified index.
     */
    public static function fromStreamByIndex($stream, $index, $path = null)
    {
        return static::reader()->readFromStreamByIndex($stream, $index, $path);
    }

    /**
     * Create the context found at the specified position in a stream.
     *
     * @param stream                  $stream   The stream.
     * @param ParserPositionInterface $position The position.
     * @param string|null             $path     The path, if known.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws ReadException              If the source code cannot be read.
     */
    public static function fromStreamByPosition(
        $stream,
        ParserPositionInterface $position,
        $path = null
    ) {
        return static::reader()
            ->readFromStreamByPosition($stream, $position, $path);
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

        $this->index = $this->buildIndices();
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
     * Get the use statements by type.
     *
     * @param UseStatementType $type The type.
     *
     * @return array<UseStatementInterface> The use statements.
     */
    public function useStatementsByType(UseStatementType $type)
    {
        return $this->typeIndex[$type->value()];
    }

    /**
     * Get the symbol associated with the supplied symbol reference's first
     * atom.
     *
     * @param SymbolReferenceInterface $symbol The symbol reference.
     * @param SymbolType|null          $type   The symbol type.
     *
     * @return QualifiedSymbolInterface|null The symbol, or null if no associated symbol exists.
     */
    public function symbolByFirstAtom(
        SymbolReferenceInterface $symbol,
        SymbolType $type = null
    ) {
        if (null === $type) {
            $useStatementType = UseStatementType::TYPE();
        } else {
            $useStatementType = UseStatementType::memberBySymbolType($type);
        }

        $index = $this->aliasIndex[$useStatementType->value()];
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
     * Get the resolution context reader.
     *
     * @return ResolutionContextReaderInterface The resolution context reader.
     */
    protected static function reader()
    {
        return ResolutionContextReader::instance();
    }

    private function buildIndices()
    {
        $typeType = UseStatementType::TYPE()->value();
        $functionType = UseStatementType::FUNCT1ON()->value();
        $constantType = UseStatementType::CONSTANT()->value();

        $this->typeIndex = array(
            $typeType => array(),
            $functionType => array(),
            $constantType => array(),
        );
        $this->aliasIndex = $this->typeIndex;

        foreach ($this->useStatements() as $useStatement) {
            $type = $useStatement->type()->value();
            $this->typeIndex[$type][] = $useStatement;

            foreach ($useStatement->clauses() as $clause) {
                $alias = $clause->effectiveAlias()->string();
                $this->aliasIndex[$type][$alias] = $clause->symbol();
            }
        }
    }

    private $primaryNamespace;
    private $useStatements;
    private $typeIndex;
    private $aliasIndex;
}
