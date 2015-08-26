<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Symbol\Factory;

use Eloquent\Cosmos\Symbol\QualifiedSymbolInterface;
use Eloquent\Pathogen\Factory\PathFactoryInterface;
use ReflectionClass;
use ReflectionFunction;

/**
 * The interface implemented by symbol factories.
 */
interface SymbolFactoryInterface extends PathFactoryInterface
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
    public function createRuntime($symbol);

    /**
     * Get the class name of the supplied object.
     *
     * @param object $object The object.
     *
     * @return QualifiedSymbolInterface The object's qualified class name.
     */
    public function createFromObject($object);

    /**
     * Get the class name of the supplied class or object reflector.
     *
     * @param ReflectionClass $class The class or object reflector.
     *
     * @return QualifiedSymbolInterface The qualified class name.
     */
    public function createFromClass(ReflectionClass $class);

    /**
     * Get the function name of the supplied function reflector.
     *
     * @param ReflectionFunction $function The function reflector.
     *
     * @return QualifiedSymbolInterface The qualified function name.
     */
    public function createFromFunction(ReflectionFunction $function);

    /**
     * Get the qualified symbol representing the global namespace.
     *
     * @return QualifiedSymbolInterface The global namespace symbol.
     */
    public function globalNamespace();
}
