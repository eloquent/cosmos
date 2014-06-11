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

use Eloquent\Cosmos\ClassName\ClassNameInterface;
use Eloquent\Cosmos\ClassName\ClassNameReference;
use Eloquent\Cosmos\ClassName\QualifiedClassName;
use Eloquent\Cosmos\ClassName\QualifiedClassNameInterface;
use Eloquent\Pathogen\Exception\InvalidPathAtomExceptionInterface;
use ReflectionClass;

/**
 * Creates class name instances.
 */
class ClassNameFactory implements ClassNameFactoryInterface
{
    /**
     * Get a static instance of this factory.
     *
     * @return ClassNameFactoryInterface The static factory.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Creates a new class name instance from its string representation.
     *
     * @param string $className The string representation of the class name.
     *
     * @return ClassNameInterface The newly created class name instance.
     */
    public function create($className)
    {
        if ('' === $className) {
            $className = QualifiedClassName::SELF_ATOM;
        }

        if (QualifiedClassName::ATOM_SEPARATOR === $className) {
            return $this->createFromAtoms(array(), true);
        }

        $isQualified = false;

        $atoms = explode(QualifiedClassName::ATOM_SEPARATOR, $className);
        $numAtoms = count($atoms);

        if ($numAtoms > 1) {
            if ('' === $atoms[0]) {
                $isQualified = true;
                array_shift($atoms);
                --$numAtoms;
            }

            if ('' === $atoms[$numAtoms - 1]) {
                array_pop($atoms);
                --$numAtoms;
            }
        }

        foreach ($atoms as $index => $atom) {
            if ('' === $atom) {
                array_splice($atoms, $index, 1);
                --$numAtoms;
            }
        }

        return $this->createFromAtoms($atoms, $isQualified);
    }

    /**
     * Creates a new class name instance from a set of class name atoms.
     *
     * Unless otherwise specified, created class names will be fully qualified.
     *
     * @param mixed<string> $atoms                The class name atoms.
     * @param boolean|null  $isQualified          True if the class name is fully qualified.
     * @param boolean|null  $hasTrailingSeparator Ignored.
     *
     * @return ClassNameInterface                The newly created class name instance.
     * @throws InvalidPathAtomExceptionInterface If any of the supplied atoms are invalid.
     */
    public function createFromAtoms(
        $atoms,
        $isQualified = null,
        $hasTrailingSeparator = null
    ) {
        if (null === $isQualified) {
            $isQualified = true;
        }

        if ($isQualified) {
            return new QualifiedClassName($atoms);
        }

        return new ClassNameReference($atoms);
    }

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
    public function createRuntime($className)
    {
        if (QualifiedClassName::ATOM_SEPARATOR !== substr($className, 0, 1)) {
            $className = QualifiedClassName::ATOM_SEPARATOR . $className;
        }

        return $this->create($className);
    }

    /**
     * Get the class name of the supplied object.
     *
     * @param object $object The object.
     *
     * @return QualifiedClassNameInterface The object's qualified class name.
     */
    public function createFromObject($object)
    {
        return $this->create('\\' . get_class($object));
    }

    /**
     * Get the class name of the supplied class or object reflector.
     *
     * @param ReflectionClass $reflector The reflector.
     *
     * @return QualifiedClassNameInterface The qualified class name.
     */
    public function createFromReflector(ReflectionClass $reflector)
    {
        return $this->create('\\' . $reflector->getName());
    }

    private static $instance;
}
