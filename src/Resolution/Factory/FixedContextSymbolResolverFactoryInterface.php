<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Factory;

use Eloquent\Cosmos\Exception\UndefinedSymbolException;
use Eloquent\Cosmos\Resolution\Context\Factory\Exception\SourceCodeReadException;
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
     * Construct a new fixed context symbol resolver by inspecting the source
     * code of the supplied object's class.
     *
     * @param object $object The object.
     *
     * @return PathResolverInterface   The newly created resolver.
     * @throws SourceCodeReadException If the source code cannot be read.
     */
    public function createFromObject($object);

    /**
     * Construct a new fixed context symbol resolver by inspecting the source
     * code of the supplied symbol.
     *
     * @param SymbolInterface|string $symbol The symbol.
     *
     * @return PathResolverInterface    The newly created resolver.
     * @throws UndefinedSymbolException If the symbol does not exist.
     * @throws SourceCodeReadException  If the source code cannot be read.
     */
    public function createFromSymbol($symbol);

    /**
     * Construct a new fixed context symbol resolver by inspecting the source
     * code of the supplied class or object reflector.
     *
     * @param ReflectionClass $class The class or object reflector.
     *
     * @return PathResolverInterface   The newly created resolver.
     * @throws SourceCodeReadException If the source code cannot be read.
     */
    public function createFromClass(ReflectionClass $class);

    /**
     * Construct a new fixed context symbol resolver by inspecting the source
     * code of the supplied function reflector.
     *
     * @param ReflectionFunction $function The function reflector.
     *
     * @return PathResolverInterface   The newly created resolver.
     * @throws SourceCodeReadException If the source code cannot be read.
     */
    public function createFromFunction(ReflectionFunction $function);
}
