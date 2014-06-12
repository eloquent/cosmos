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
use Eloquent\Cosmos\Resolution\Context\Factory\ResolutionContextFactory;
use Eloquent\Cosmos\Resolution\Context\Factory\ResolutionContextFactoryInterface;
use Eloquent\Cosmos\Resolution\Context\ResolutionContextInterface;
use Eloquent\Cosmos\Resolution\FixedContextSymbolResolver;
use Eloquent\Cosmos\Resolution\SymbolResolver;
use Eloquent\Cosmos\Resolution\SymbolResolverInterface;
use Eloquent\Cosmos\Symbol\SymbolInterface;
use Eloquent\Pathogen\Resolver\PathResolverInterface;
use ReflectionClass;
use ReflectionFunction;

/**
 * Creates fixed context symbol resolvers.
 */
class FixedContextSymbolResolverFactory implements
    FixedContextSymbolResolverFactoryInterface
{
    /**
     * Get a static instance of this factory.
     *
     * @return FixedContextSymbolResolverFactoryInterface The static factory.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Construct a new fixed context symbol resolver factory.
     *
     * @param SymbolResolverInterface|null           $resolver       The internal symbol resolver to use.
     * @param ResolutionContextFactoryInterface|null $contextFactory The resolution context factory to use.
     */
    public function __construct(
        SymbolResolverInterface $resolver = null,
        ResolutionContextFactoryInterface $contextFactory = null
    ) {
        if (null === $resolver) {
            $resolver = SymbolResolver::instance();
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
     * @return SymbolResolverInterface The internal resolver.
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
     * Construct a new fixed context symbol resolver.
     *
     * @param ResolutionContextInterface|null $context The resolution context.
     *
     * @return PathResolverInterface The newly created resolver.
     */
    public function create(ResolutionContextInterface $context = null)
    {
        return new FixedContextSymbolResolver($context, $this->resolver());
    }

    /**
     * Construct a new fixed context symbol resolver by inspecting the source
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
     * Construct a new fixed context symbol resolver by inspecting the source
     * code of the supplied symbol.
     *
     * @param SymbolInterface|string $symbol The symbol.
     *
     * @return PathResolverInterface    The newly created resolver.
     * @throws UndefinedSymbolException If the symbol does not exist.
     * @throws SourceCodeReadException  If the source code cannot be read.
     */
    public function createFromSymbol($symbol)
    {
        return $this
            ->create($this->contextFactory()->createFromSymbol($symbol));
    }

    /**
     * Construct a new fixed context symbol resolver by inspecting the source
     * code of the supplied class or object reflector.
     *
     * @param ReflectionClass $class The class or object reflector.
     *
     * @return PathResolverInterface   The newly created resolver.
     * @throws SourceCodeReadException If the source code cannot be read.
     */
    public function createFromClass(ReflectionClass $class)
    {
        return $this->create($this->contextFactory()->createFromClass($class));
    }

    /**
     * Construct a new fixed context symbol resolver by inspecting the source
     * code of the supplied function reflector.
     *
     * @param ReflectionFunction $function The function reflector.
     *
     * @return PathResolverInterface   The newly created resolver.
     * @throws SourceCodeReadException If the source code cannot be read.
     */
    public function createFromFunction(ReflectionFunction $function)
    {
        return $this
            ->create($this->contextFactory()->createFromFunction($function));
    }

    private static $instance;
    private $resolver;
    private $contextFactory;
}
