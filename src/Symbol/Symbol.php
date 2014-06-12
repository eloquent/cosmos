<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Symbol;

use Eloquent\Cosmos\Symbol\Factory\SymbolFactory;
use Eloquent\Cosmos\Symbol\Factory\SymbolFactoryInterface;
use Eloquent\Pathogen\Path;
use ReflectionClass;
use ReflectionFunction;

/**
 * A static utility class for constructing symbols.
 *
 * Do not use this class in type hints; use SymbolInterface instead.
 */
abstract class Symbol extends Path
{
    /**
     * Creates a new qualified symbol from its string representation, regardless
     * of whether it starts with a namespace separator.
     *
     * This method emulates the manner in which symbols are typically
     * interpreted at run time.
     *
     * @param string $symbol The string representation of the symbol.
     *
     * @return QualifiedSymbolInterface The newly created qualified symbol instance.
     */
    public static function fromRuntimeString($symbol)
    {
        return static::factory()->createRuntime($symbol);
    }

    /**
     * Get the class name of the supplied object.
     *
     * @param object $object The object.
     *
     * @return QualifiedSymbolInterface The object's qualified class name.
     */
    public static function fromObject($object)
    {
        return static::factory()->createFromObject($object);
    }

    /**
     * Get the class name of the supplied class or object reflector.
     *
     * @param ReflectionClass $class The class or object reflector.
     *
     * @return QualifiedSymbolInterface The qualified class name.
     */
    public static function fromClass(ReflectionClass $class)
    {
        return static::factory()->createFromClass($class);
    }

    /**
     * Get the function name of the supplied function reflector.
     *
     * @param ReflectionFunction $function The function reflector.
     *
     * @return QualifiedSymbolInterface The qualified function name.
     */
    public static function fromFunction(ReflectionFunction $function)
    {
        return static::factory()->createFromFunction($function);
    }

    /**
     * Get the qualified symbol representing the global namespace.
     *
     * @return QualifiedSymbolInterface The global namespace symbol.
     */
    public static function globalNamespace()
    {
        return static::factory()->globalNamespace();
    }

    /**
     * Get the symbol factory.
     *
     * @return SymbolFactoryInterface The symbol factory.
     */
    protected static function factory()
    {
        return SymbolFactory::instance();
    }
}
