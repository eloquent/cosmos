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

use Eloquent\Cosmos\ClassName\ClassNameInterface;
use Eloquent\Cosmos\Exception\UndefinedClassException;
use Eloquent\Cosmos\Resolution\Context\Factory\Exception\SourceCodeReadException;
use Eloquent\Cosmos\Resolution\Context\ResolutionContextInterface;
use Eloquent\Pathogen\Resolver\PathResolverInterface;
use ReflectionClass;

/**
 * The interface implemented by fixed context class name resolver factories.
 */
interface FixedContextClassNameResolverFactoryInterface
{
    /**
     * Construct a new fixed context class name resolver.
     *
     * @param ResolutionContextInterface|null $context The resolution context.
     *
     * @return PathResolverInterface The newly created resolver.
     */
    public function create(ResolutionContextInterface $context = null);

    /**
     * Construct a new fixed context class name resolver by inspecting the
     * source code of the supplied object's class.
     *
     * @param object $object The object.
     *
     * @return PathResolverInterface   The newly created resolver.
     * @throws SourceCodeReadException If the source code cannot be read.
     */
    public function createFromObject($object);

    /**
     * Construct a new fixed context class name resolver by inspecting the
     * source code of the supplied class.
     *
     * @param ClassNameInterface|string $className The class.
     *
     * @return PathResolverInterface   The newly created resolver.
     * @throws UndefinedClassException If the class does not exist.
     * @throws SourceCodeReadException If the source code cannot be read.
     */
    public function createFromClass($className);

    /**
     * Construct a new fixed context class name resolver by inspecting the
     * source code of the supplied class reflector.
     *
     * @param ReflectionClass $reflector The reflector.
     *
     * @return PathResolverInterface   The newly created resolver.
     * @throws SourceCodeReadException If the source code cannot be read.
     */
    public function createFromReflector(ReflectionClass $reflector);
}
