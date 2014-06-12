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

use Eloquent\Cosmos\ClassName\ClassNameInterface;
use Eloquent\Cosmos\ClassName\QualifiedClassNameInterface;
use Eloquent\Cosmos\Exception\UndefinedClassException;
use Eloquent\Cosmos\Resolution\Context\Factory\Exception\SourceCodeReadException;
use Eloquent\Cosmos\Resolution\Context\ResolutionContext;
use Eloquent\Cosmos\Resolution\Context\ResolutionContextInterface;
use Eloquent\Cosmos\Resolution\Factory\FixedContextClassNameResolverFactory;
use Eloquent\Cosmos\Resolution\Factory\FixedContextClassNameResolverFactoryInterface;
use Eloquent\Pathogen\PathInterface;
use Eloquent\Pathogen\Resolver\PathResolverInterface;
use ReflectionClass;

/**
 * Resolves class names against a fixed context.
 */
class FixedContextClassNameResolver implements PathResolverInterface
{
    /**
     * Construct a new fixed context class name resolver by inspecting the
     * source code of the supplied object's class.
     *
     * @param object $object The object.
     *
     * @return PathResolverInterface   The newly created resolver.
     * @throws SourceCodeReadException If the source code cannot be read.
     */
    public static function fromObject($object)
    {
        return static::factory()->createFromObject($object);
    }

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
    public static function fromClass($className)
    {
        return static::factory()->createFromClass($className);
    }

    /**
     * Construct a new fixed context class name resolver by inspecting the
     * source code of the supplied class reflector.
     *
     * @param ReflectionClass $reflector The reflector.
     *
     * @return PathResolverInterface   The newly created resolver.
     * @throws SourceCodeReadException If the source code cannot be read.
     */
    public static function fromReflector(ReflectionClass $reflector)
    {
        return static::factory()->createFromReflector($reflector);
    }

    /**
     * Construct a new fixed context class name resolver.
     *
     * @param ResolutionContextInterface|null $context  The resolution context.
     * @param ClassNameResolverInterface|null $resolver The internal class name resolver to use.
     */
    public function __construct(
        ResolutionContextInterface $context = null,
        ClassNameResolverInterface $resolver = null
    ) {
        if (null === $context) {
            $context = new ResolutionContext;
        }
        if (null === $resolver) {
            $resolver = ClassNameResolver::instance();
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
     * @return ClassNameResolverInterface The internal resolver.
     */
    public function resolver()
    {
        return $this->resolver;
    }

    /**
     * Resolve a class name against a fixed context.
     *
     * Class names that are already qualified will be returned unaltered.
     *
     * @param PathInterface $className The class name to resolve.
     *
     * @return QualifiedClassNameInterface The resolved, qualified class name.
     */
    public function resolve(PathInterface $className)
    {
        return $this->resolver()
            ->resolveAgainstContext($this->context(), $className);
    }

    /**
     * Get the fixed context class name resolver factory.
     *
     * @return FixedContextClassNameResolverFactoryInterface The fixed context class name resolver factory.
     */
    protected static function factory()
    {
        return FixedContextClassNameResolverFactory::instance();
    }

    private $context;
    private $resolver;
}
