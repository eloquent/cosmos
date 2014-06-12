<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\ClassName;

use Eloquent\Cosmos\ClassName\Factory\ClassNameFactory;
use Eloquent\Cosmos\ClassName\Factory\ClassNameFactoryInterface;
use Eloquent\Pathogen\Path;
use ReflectionClass;

/**
 * A static utility class for constructing class names.
 *
 * Do not use this class in type hints; use ClassNameInterface instead.
 */
abstract class ClassName extends Path
{
    /**
     * Creates a new qualified class name instance from its string
     * representation, regardless of whether it starts with a namespace
     * separator.
     *
     * This method emulates the manner in which class names are typically
     * interpreted at run time.
     *
     * @param string $className The string representation of the class name.
     *
     * @return QualifiedClassNameInterface The newly created qualified class name instance.
     */
    public static function fromRuntimeString($className)
    {
        return static::factory()->createRuntime($className);
    }

    /**
     * Get the class name of the supplied object.
     *
     * @param object $object The object.
     *
     * @return QualifiedClassNameInterface The object's qualified class name.
     */
    public static function fromObject($object)
    {
        return static::factory()->createFromObject($object);
    }

    /**
     * Get the class name of the supplied class or object reflector.
     *
     * @param ReflectionClass $reflector The reflector.
     *
     * @return QualifiedClassNameInterface The qualified class name.
     */
    public static function fromReflector(ReflectionClass $reflector)
    {
        return static::factory()->createFromReflector($reflector);
    }

    /**
     * Get the qualified class name representing the global namespace.
     *
     * @return QualifiedClassNameInterface The global namespace class name.
     */
    public static function globalNamespace()
    {
        return static::factory()->globalNamespace();
    }

    /**
     * Get the class name factory.
     *
     * @return ClassNameFactoryInterface The class name factory.
     */
    protected static function factory()
    {
        return ClassNameFactory::instance();
    }
}
