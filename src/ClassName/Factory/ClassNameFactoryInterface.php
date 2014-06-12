<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\ClassName\Factory;

use Eloquent\Cosmos\ClassName\QualifiedClassNameInterface;
use Eloquent\Pathogen\Factory\PathFactoryInterface;
use ReflectionClass;

/**
 * The interface used to identify class name factories.
 */
interface ClassNameFactoryInterface extends PathFactoryInterface
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
    public function createRuntime($className);

    /**
     * Get the class name of the supplied object.
     *
     * @param object $object The object.
     *
     * @return QualifiedClassNameInterface The object's qualified class name.
     */
    public function createFromObject($object);

    /**
     * Get the class name of the supplied class or object reflector.
     *
     * @param ReflectionClass $reflector The reflector.
     *
     * @return QualifiedClassNameInterface The qualified class name.
     */
    public function createFromReflector(ReflectionClass $reflector);

    /**
     * Get the qualified class name representing the global namespace.
     *
     * @return QualifiedClassNameInterface The global namespace class name.
     */
    public function globalNamespace();
}
