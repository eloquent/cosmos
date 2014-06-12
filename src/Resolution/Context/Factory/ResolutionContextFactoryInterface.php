<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Context\Factory;

use Eloquent\Cosmos\Exception\UndefinedSymbolException;
use Eloquent\Cosmos\Resolution\Context\Factory\Exception\SourceCodeReadException;
use Eloquent\Cosmos\Resolution\Context\ResolutionContextInterface;
use Eloquent\Cosmos\Symbol\QualifiedSymbolInterface;
use Eloquent\Cosmos\Symbol\SymbolInterface;
use Eloquent\Cosmos\UseStatement\UseStatementInterface;
use ReflectionClass;
use ReflectionFunction;

/**
 * The interface implemented by symbol resolution context factories.
 */
interface ResolutionContextFactoryInterface
{
    /**
     * Construct a new symbol resolution context.
     *
     * @param QualifiedSymbolInterface|null     $primaryNamespace The namespace.
     * @param array<UseStatementInterface>|null $useStatements    The use statements.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     */
    public function create(
        QualifiedSymbolInterface $primaryNamespace = null,
        array $useStatements = null
    );

    /**
     * Construct a new symbol resolution context by inspecting the source code
     * of the supplied object's class.
     *
     * @param object $object The object.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws SourceCodeReadException    If the source code cannot be read.
     */
    public function createFromObject($object);

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
    public function createFromSymbol($symbol);

    /**
     * Construct a new symbol resolution context by inspecting the source code
     * of the supplied class or object reflector.
     *
     * @param ReflectionClass $class The class or object reflector.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws SourceCodeReadException    If the source code cannot be read.
     */
    public function createFromClass(ReflectionClass $class);

    /**
     * Construct a new symbol resolution context by inspecting the source code
     * of the supplied function reflector.
     *
     * @param ReflectionFunction $function The function reflector.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws SourceCodeReadException    If the source code cannot be read.
     */
    public function createFromFunction(ReflectionFunction $function);
}
