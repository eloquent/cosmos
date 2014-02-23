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
     * Get the class name factory.
     *
     * @return Factory\ClassNameFactoryInterface The class name factory.
     */
    protected static function factory()
    {
        return Factory\ClassNameFactory::instance();
    }
}
