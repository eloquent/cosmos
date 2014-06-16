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

use Eloquent\Cosmos\Exception\UndefinedSymbolException;
use Eloquent\Cosmos\Resolution\Context\Factory\Exception\SourceCodeReadException;
use Eloquent\Cosmos\Resolution\Context\Factory\ResolutionContextFactory;
use Eloquent\Cosmos\Resolution\Context\Factory\ResolutionContextFactoryInterface;
use Eloquent\Cosmos\Symbol\Factory\SymbolFactory;
use Eloquent\Cosmos\Symbol\Factory\SymbolFactoryInterface;
use Eloquent\Cosmos\Symbol\QualifiedSymbolInterface;
use Eloquent\Cosmos\Symbol\SymbolInterface;
use Eloquent\Cosmos\Symbol\SymbolReferenceInterface;
use Eloquent\Cosmos\UseStatement\UseStatementInterface;
use ReflectionClass;
use ReflectionFunction;

/**
 * Represents a combined namespace and set of use statements.
 */
class ResolutionContext implements ResolutionContextInterface
{
    /**
     * Construct a new symbol resolution context by inspecting the source code
     * of the supplied object's class.
     *
     * @param object $object The object.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws SourceCodeReadException    If the source code cannot be read.
     */
    public static function fromObject($object)
    {
        return static::factory()->createFromObject($object);
    }

    /**
     * Construct a new symbol resolution context by inspecting the source code
     * of the supplied symbol.
     *
     * @param SymbolInterface|string $symbol The symbol.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws UndefinedSymbolException   If the symbol does not exist.
     * @throws SourceCodeReadException    If the source code cannot be read.
     */
    public static function fromSymbol($symbol)
    {
        return static::factory()->createFromSymbol($symbol);
    }

    /**
     * Construct a new symbol resolution context by inspecting the source code
     * of the supplied function symbol.
     *
     * @param SymbolInterface|string $symbol The symbol.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws UndefinedSymbolException   If the symbol does not exist.
     * @throws SourceCodeReadException    If the source code cannot be read.
     */
    public static function fromFunctionSymbol($symbol)
    {
        return static::factory()->createFromFunctionSymbol($symbol);
    }

    /**
     * Construct a new symbol resolution context by inspecting the source code
     * of the supplied class or object reflector.
     *
     * @param ReflectionClass $class The class or object reflector.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws SourceCodeReadException    If the source code cannot be read.
     */
    public static function fromClass(ReflectionClass $class)
    {
        return static::factory()->createFromClass($class);
    }

    /**
     * Construct a new symbol resolution context by inspecting the source code
     * of the supplied function reflector.
     *
     * @param ReflectionFunction $function The function reflector.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws SourceCodeReadException    If the source code cannot be read.
     */
    public static function fromFunction(ReflectionFunction $function)
    {
        return static::factory()->createFromFunction($function);
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
