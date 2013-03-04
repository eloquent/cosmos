<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Cosmos;

use Eloquent\Equality\Comparator;

class ClassNameResolver
{
    /**
     * @param ClassName|null          $namespaceName
     * @param array<array<ClassName>> $usedClasses
     */
    public function __construct(
        ClassName $namespaceName = null,
        array $usedClasses = array(),
        Comparator $comparator = null
    ) {
        if (null !== $namespaceName) {
            $namespaceName = $namespaceName->toAbsolute();
        }
        if (null === $comparator) {
            $comparator = new Comparator;
        }

        $this->namespaceName = $namespaceName;
        $this->usedClasses = $this->normalizeUsedClasses($usedClasses);
        $this->comparator = $comparator;
    }

    /**
     * @return ClassName
     */
    public function namespaceName()
    {
        return $this->namespaceName;
    }

    /**
     * @return array<tuple<ClassName,ClassName>>
     */
    public function usedClasses()
    {
        return $this->usedClasses;
    }

    /**
     * @return Comparator
     */
    public function comparator()
    {
        return $this->comparator;
    }

    /**
     * @param ClassName $className
     *
     * @return ClassName
     */
    public function resolve(ClassName $className)
    {
        if ($className->isAbsolute()) {
            return $className;
        } elseif ($className->isShortName()) {
            $usedClass = $this->usedClass($className);
            if (null !== $usedClass) {
                return $usedClass;
            }
        }

        if (null !== $this->namespaceName()) {
            return $this->namespaceName()->join($className);
        }

        return $className->toAbsolute();
    }

    /**
     * @param ClassName $className
     *
     * @return ClassName
     */
    public function shorten(ClassName $className)
    {
        if (!$className->isAbsolute()) {
            return $className;
        }

        foreach ($this->usedClasses() as $tuple) {
            list($usedClass, $as) = $tuple;
            if ($this->comparator()->equals($usedClass, $className)) {
                return $as;
            }
        }

        if (
            null !== $this->namespaceName() &&
            $this->namespaceName()->hasDescendant($className)
        ) {
            return $className->stripNamespace($this->namespaceName());
        }

        return $className;
    }

    /**
     * @param array<array<ClassName>> $usedClasses
     *
     * @return array<tuple<ClassName,ClassName>>
     */
    protected function normalizeUsedClasses(array $usedClasses)
    {
        $normalized = array();
        foreach ($usedClasses as $tuple) {
            $tuple[0] = $tuple[0]->toAbsolute();

            if (array_key_exists(1, $tuple)) {
                if (!$tuple[1]->isShortName()) {
                    throw new Exception\InvalidUsedClassAliasException($tuple[1]);
                }

                $normalized[] = $tuple;
            } else {
                $normalized[] = array($tuple[0], $tuple[0]->shortName());
            }
        }

        return $normalized;
    }

    /**
     * @param ClassName $className
     *
     * @return ClassName|null
     */
    protected function usedClass(ClassName $className)
    {
        foreach ($this->usedClasses() as $tuple) {
            list($usedClass, $as) = $tuple;
            if ($this->comparator()->equals($as, $className)) {
                return $usedClass;
            }
        }

        return null;
    }

    private $namespaceName;
    private $usedClasses;
}
