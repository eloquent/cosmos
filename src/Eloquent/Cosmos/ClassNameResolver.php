<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2012 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Cosmos;

class ClassNameResolver
{
    /**
     * @param string $qualifiedName
     *
     * @return string
     */
    public static function shortName($qualifiedName)
    {
        $parts = explode(static::NAMESPACE_SEPARATOR, $qualifiedName);

        return array_pop($parts);
    }

    /**
     * @param string|null               $namespaceName
     * @param array<string,string|null> $usedClasses
     */
    public function __construct($namespaceName = null, array $usedClasses = array())
    {
        $this->namespaceName = $this->normalizeQualifiedName($namespaceName);
        $this->usedClasses = $this->normalizeUsedClasses($usedClasses);
    }

    /**
     * @return string
     */
    public function namespaceName()
    {
        return $this->namespaceName;
    }

    /**
     * @return array<string,string>
     */
    public function usedClasses()
    {
        return $this->usedClasses;
    }

    /**
     * @param string $className
     *
     * @return string
     */
    public function resolve($className)
    {
        if (!$className) {
            throw new Exception\InvalidClassNameException($className);
        }

        $parts = explode(static::NAMESPACE_SEPARATOR, $className);
        if (1 === count($parts)) {
            $usedClass = $this->usedClass($parts[0]);
            if (null !== $usedClass) {
                return $usedClass;
            }
        } elseif ('' === $parts[0]) {
            array_shift($parts);

            return implode(static::NAMESPACE_SEPARATOR, $parts);
        }

        if (null !== $this->namespaceName()) {
            array_unshift($parts, $this->namespaceName());
        }

        return implode(static::NAMESPACE_SEPARATOR, $parts);
    }

    /**
     * @param string $qualifiedName
     *
     * @return string
     */
    public function shorten($qualifiedName)
    {
        if (!$qualifiedName) {
            throw new Exception\InvalidClassNameException($qualifiedName);
        }
        $qualifiedName = $this->normalizeQualifiedName($qualifiedName);

        foreach ($this->usedClasses() as $usedClass => $as) {
            if ($usedClass === $qualifiedName) {
                return $as;
            }
        }

        $namespaceNameLength = strlen($this->namespaceName());
        if (
            $this->namespaceName() ===
            substr($qualifiedName, 0, $namespaceNameLength)
        ) {
            return substr($qualifiedName, $namespaceNameLength + 1);
        }

        return sprintf('%s%s', static::NAMESPACE_SEPARATOR, $qualifiedName);
    }

    const NAMESPACE_SEPARATOR = '\\';

    /**
     * @param string $qualifiedName
     *
     * @return string
     */
    protected function normalizeQualifiedName($qualifiedName)
    {
        if (static::NAMESPACE_SEPARATOR === substr($qualifiedName, 0, 1)) {
            $qualifiedName = substr($qualifiedName, 1);
        }

        return $qualifiedName;
    }

    /**
     * @param array<string,string|null> $usedClasses
     *
     * @return array<string,string>
     */
    protected function normalizeUsedClasses(array $usedClasses)
    {
        $normalized = array();
        foreach ($usedClasses as $qualifiedName => $as) {
            $qualifiedName = $this->normalizeQualifiedName($qualifiedName);
            if (null === $as) {
                $normalized[$qualifiedName] = static::shortName($qualifiedName);
            } else {
                $normalized[$qualifiedName] = $as;
            }
        }

        return $normalized;
    }

    /**
     * @param string $className
     *
     * @return string|null
     */
    protected function usedClass($className)
    {
        return array_search($className, $this->usedClasses, true) ?: null;
    }

    private $namespaceName;
    private $usedClasses;
}
