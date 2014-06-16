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

use Eloquent\Cosmos\Exception\UndefinedSymbolException;
use Eloquent\Cosmos\Resolution\Context\Factory\Exception\SourceCodeReadException;
use Eloquent\Cosmos\Resolution\Context\ResolutionContext;
use Eloquent\Cosmos\Resolution\Context\ResolutionContextInterface;
use Eloquent\Cosmos\Resolution\Factory\FixedContextSymbolResolverFactory;
use Eloquent\Cosmos\Resolution\Factory\FixedContextSymbolResolverFactoryInterface;
use Eloquent\Cosmos\Symbol\QualifiedSymbolInterface;
use Eloquent\Cosmos\Symbol\SymbolInterface;
use Eloquent\Pathogen\PathInterface;
use Eloquent\Pathogen\Resolver\PathResolverInterface;
use ReflectionClass;
use ReflectionFunction;

/**
 * Resolves symbols against a fixed context.
 */
class FixedContextSymbolResolver implements PathResolverInterface
{
    /**
     * Construct a new fixed context symbol resolver by inspecting the source
     * code of the supplied object's class.
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
     * Construct a new fixed context symbol resolver by inspecting the source
     * code of the supplied class, interface, or trait symbol.
     *
     * @param SymbolInterface|string $symbol The symbol.
     *
     * @return PathResolverInterface    The newly created resolver.
     * @throws UndefinedSymbolException If the symbol does not exist.
     * @throws SourceCodeReadException  If the source code cannot be read.
     */
    public static function fromSymbol($symbol)
    {
        return static::factory()->createFromSymbol($symbol);
    }

    /**
     * Construct a new fixed context symbol resolver by inspecting the source
     * code of the supplied function symbol.
     *
     * @param SymbolInterface|string $symbol The symbol.
     *
     * @return PathResolverInterface    The newly created resolver.
     * @throws UndefinedSymbolException If the symbol does not exist.
     * @throws SourceCodeReadException  If the source code cannot be read.
     */
    public static function fromFunctionSymbol($symbol)
    {
        return static::factory()->createFromFunctionSymbol($symbol);
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
    public static function fromClass(ReflectionClass $class)
    {
        return static::factory()->createFromClass($class);
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
    public static function fromFunction(ReflectionFunction $function)
    {
        return static::factory()->createFromFunction($function);
    }

    /**
     * Construct a new fixed context symbol resolver.
     *
     * @param ResolutionContextInterface|null $context  The resolution context.
     * @param SymbolResolverInterface|null    $resolver The internal symbol resolver to use.
     */
    public function __construct(
        ResolutionContextInterface $context = null,
        SymbolResolverInterface $resolver = null
    ) {
        if (null === $context) {
            $context = new ResolutionContext;
        }
        if (null === $resolver) {
            $resolver = SymbolResolver::instance();
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
     * @return SymbolResolverInterface The internal resolver.
     */
    public function resolver()
    {
        return $this->resolver;
    }

    /**
     * Resolve a symbol against a fixed context.
     *
     * Symbols that are already qualified will be returned unaltered.
     *
     * @param PathInterface $symbol The symbol to resolve.
     *
     * @return QualifiedSymbolInterface The resolved, qualified symbol.
     */
    public function resolve(PathInterface $symbol)
    {
        return $this->resolver()
            ->resolveAgainstContext($this->context(), $symbol);
    }

    /**
     * Get the fixed context symbol resolver factory.
     *
     * @return FixedContextSymbolResolverFactoryInterface The fixed context symbol resolver factory.
     */
    protected static function factory()
    {
        return FixedContextSymbolResolverFactory::instance();
    }

    private $context;
    private $resolver;
}
