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

use Eloquent\Cosmos\ClassName\QualifiedClassNameInterface;
use Eloquent\Cosmos\Exception\UndefinedClassException;
use Eloquent\Cosmos\Resolution\ClassNameResolver;
use Eloquent\Cosmos\Resolution\ClassNameResolverInterface;
use Eloquent\Cosmos\Resolution\Context\Factory\Exception\SourceCodeReadException;
use Eloquent\Cosmos\Resolution\Context\Factory\ResolutionContextFactory;
use Eloquent\Cosmos\Resolution\Context\Factory\ResolutionContextFactoryInterface;
use Eloquent\Cosmos\Resolution\Context\ResolutionContextInterface;
use Eloquent\Cosmos\Resolution\FixedContextClassNameResolver;
use Eloquent\Pathogen\Resolver\PathResolverInterface;
use ReflectionClass;

/**
 * Creates fixed context class name resolvers.
 */
class FixedContextClassNameResolverFactory implements
    FixedContextClassNameResolverFactoryInterface
{
    /**
     * Get a static instance of this factory.
     *
     * @return FixedContextClassNameResolverFactoryInterface The static factory.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Construct a new fixed context class name resolver factory.
     *
     * @param ClassNameResolverInterface|null        $resolver       The internal class name resolver to use.
     * @param ResolutionContextFactoryInterface|null $contextFactory The resolution context factory to use.
     */
    public function __construct(
        ClassNameResolverInterface $resolver = null,
        ResolutionContextFactoryInterface $contextFactory = null
    ) {
        if (null === $resolver) {
            $resolver = ClassNameResolver::instance();
        }
        if (null === $contextFactory) {
            $contextFactory = ResolutionContextFactory::instance();
        }

        $this->resolver = $resolver;
        $this->contextFactory = $contextFactory;
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
     * Get the resolution context factory.
     *
     * @return ResolutionContextFactoryInterface The resolution context factory.
     */
    public function contextFactory()
    {
        return $this->contextFactory;
    }

    /**
     * Construct a new class name resolution context.
     *
     * @param ResolutionContextInterface|null $context The resolution context.
     *
     * @return PathResolverInterface The newly created resolver.
     */
    public function create(ResolutionContextInterface $context = null)
    {
        return new FixedContextClassNameResolver($context, $this->resolver());
    }

    /**
     * Construct a new class name resolution context by inspecting the source
     * code of the supplied object's class.
     *
     * @param object $object The object.
     *
     * @return PathResolverInterface   The newly created resolver.
     * @throws SourceCodeReadException If the source code cannot be read.
     */
    public function createFromObject($object)
    {
        return $this
            ->create($this->contextFactory()->createFromObject($object));
    }

    /**
     * Construct a new class name resolution context by inspecting the source
     * code of the supplied class.
     *
     * @param QualifiedClassNameInterface $className The class.
     *
     * @return PathResolverInterface   The newly created resolver.
     * @throws UndefinedClassException If the class does not exist.
     * @throws SourceCodeReadException If the source code cannot be read.
     */
    public function createFromClass(QualifiedClassNameInterface $className)
    {
        return $this
            ->create($this->contextFactory()->createFromClass($className));
    }

    /**
     * Construct a new class name resolution context by inspecting the source
     * code of the supplied class reflector.
     *
     * @param ReflectionClass $reflector The reflector.
     *
     * @return PathResolverInterface   The newly created resolver.
     * @throws SourceCodeReadException If the source code cannot be read.
     */
    public function createFromReflector(ReflectionClass $reflector)
    {
        return $this
            ->create($this->contextFactory()->createFromReflector($reflector));
    }

    private static $instance;
    private $resolver;
    private $contextFactory;
}
