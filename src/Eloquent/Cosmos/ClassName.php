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

use Eloquent\Equality\Comparator;
use Icecave\Isolator\Isolator;

class ClassName
{
    /**
     * @param string $className
     *
     * @return ClassName
     */
    public static function fromString($className)
    {
        $atoms = explode(static::NAMESPACE_SEPARATOR, $className);
        $isAbsolute = false;
        if ('' === $atoms[0]) {
            $isAbsolute = true;
            array_shift($atoms);
        }

        return static::fromAtoms($atoms, $isAbsolute);
    }

    /**
     * @param string $className
     *
     * @return ClassName
     */
    public static function fromAtoms(array $atoms, $isAbsolute = false)
    {
        return new static($atoms, $isAbsolute);
    }

    /**
     * @return array<integer,string>
     */
    public function atoms()
    {
        return $this->atoms;
    }

    /**
     * @return boolean
     */
    public function isAbsolute()
    {
        return $this->isAbsolute;
    }

    /**
     * @return boolean
     */
    public function isShortName()
    {
        return !$this->isAbsolute() && 1 === count($this->atoms());
    }

    /**
     * @param ClassName $className
     *
     * @return ClassName
     */
    public function join(ClassName $className)
    {
        if ($className->isAbsolute()) {
            throw new Exception\AbsoluteJoinException($className);
        }

        return static::fromAtoms(
            array_merge(
                $this->atoms(),
                $className->atoms()
            ),
            $this->isAbsolute()
        );
    }

    /**
     * @return boolean
     */
    public function hasParent()
    {
        return count($this->atoms()) > 1;
    }

    /**
     * @return ClassName
     */
    public function parent()
    {
        if (!$this->hasParent()) {
            throw new Exception\ParentException($this);
        }

        $atoms = $this->atoms();
        array_pop($atoms);

        return static::fromAtoms($atoms, $this->isAbsolute());
    }

    /**
     * @return ClassName
     */
    public function shortName()
    {
        $atoms = $this->atoms();
        $atom = array_pop($atoms);

        return static::fromAtoms(array($atom), false);
    }

    /**
     * @return ClassName
     */
    public function toAbsolute()
    {
        if (!$this->isAbsolute()) {
            return static::fromAtoms($this->atoms(), true);
        }

        return $this;
    }

    /**
     * @return ClassName
     */
    public function toRelative()
    {
        if ($this->isAbsolute()) {
            return static::fromAtoms($this->atoms(), false);
        }

        return $this;
    }

    /**
     * @param ClassName       $className
     * @param Comparator|null $comparator
     *
     * @return boolean
     */
    public function hasDescendant(ClassName $className, Comparator $comparator = null)
    {
        if ($this->isAbsolute() !== $className->isAbsolute()) {
            return false;
        }

        if (null === $comparator) {
            $comparator = new Comparator;
        }

        while ($className->hasParent()) {
            $className = $className->parent();
            if ($comparator->equals($this, $className)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param ClassName $namespaceName
     *
     * @return ClassName
     */
    public function stripNamespace(ClassName $namespaceName)
    {
        if (!$namespaceName->hasDescendant($this)) {
            throw new Exception\NamespaceMismatchException(
                $this,
                $namespaceName
            );
        }

        $atoms = $this->atoms();
        array_splice($atoms, 0, count($namespaceName->atoms()));

        return static::fromAtoms($atoms, false);
    }

    /**
     * @param boolean|null $useAutoload
     * @param Isolator     $isolator
     *
     * @return boolean
     */
    public function exists($useAutoload = null, Isolator $isolator = null)
    {
        if (null === $useAutoload) {
            $useAutoload = true;
        }
        $isolator = Isolator::get($isolator);

        return $isolator->class_exists($this->string(), $useAutoload);
    }

    /**
     * @return string
     */
    public function string()
    {
        $className = implode(static::NAMESPACE_SEPARATOR, $this->atoms());
        if ($this->isAbsolute()) {
            $className = static::NAMESPACE_SEPARATOR.$className;
        }

        return $className;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->string();
    }

    const NAMESPACE_SEPARATOR = '\\';

    /**
     * @param array<integer,string> $atoms
     * @param boolean               $isAbsolute
     */
    protected function __construct(array $atoms, $isAbsolute)
    {
        if (count($atoms) < 1) {
            throw new Exception\EmptyClassNameException;
        }
        foreach ($atoms as $atom) {
            if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $atom)) {
                throw new Exception\InvalidClassNameAtomException($atom);
            }
        }

        $this->atoms = $atoms;
        $this->isAbsolute = $isAbsolute;
    }

    private $atoms;
    private $isAbsolute;
}
