<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Symbol;

use Eloquent\Cosmos\Exception\InvalidSymbolAtomException;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionFunction;

/**
 * Represents a symbol.
 *
 * @api
 */
class Symbol implements SymbolInterface
{
    /**
     * Create a new symbol from its string representation.
     *
     * @api
     *
     * @param string $symbol The string representation.
     *
     * @return SymbolInterface            The newly created symbol.
     * @throws InvalidSymbolAtomException If the supplied string is invalid.
     */
    public static function fromString($symbol)
    {
        $atoms = \explode('\\', $symbol);
        $isQualified = false;

        if (\count($atoms) > 1 && '' === $atoms[0]) {
            \array_shift($atoms);
            $isQualified = true;
        }

        return new self($atoms, $isQualified);
    }

    /**
     * Create a new symbol from its string representation, but always return a
     * qualified symbol.
     *
     * This method emulates the manner in which symbols are typically
     * interpreted at run time.
     *
     * @api
     *
     * @param string $symbol The string representation.
     *
     * @return SymbolInterface            The newly created symbol.
     * @throws InvalidSymbolAtomException If the supplied string is invalid.
     */
    public static function fromRuntimeString($symbol)
    {
        $atoms = \explode('\\', $symbol);

        if (\count($atoms) > 1 && '' === $atoms[0]) {
            \array_shift($atoms);
        }

        return new self($atoms, true);
    }

    /**
     * Create a new symbol from a set of atoms.
     *
     * Unless otherwise specified, created symbols will be qualified.
     *
     * @api
     *
     * @param array<string> $atoms       The atoms.
     * @param boolean       $isQualified True if qualified.
     *
     * @return SymbolInterface            The newly created symbol.
     * @throws InvalidSymbolAtomException If any of the supplied atoms are invalid.
     */
    public static function fromAtoms(array $atoms, $isQualified = true)
    {
        return new self($atoms, $isQualified);
    }

    /**
     * Get the class name of the supplied object.
     *
     * @api
     *
     * @param object $object The object.
     *
     * @return SymbolInterface The newly created symbol.
     */
    public static function fromObject($object)
    {
        return new self(\explode('\\', \get_class($object)), true);
    }

    /**
     * Get the class name of the supplied class or object reflector.
     *
     * @api
     *
     * @param ReflectionClass $class The class or object reflector.
     *
     * @return SymbolInterface The newly created symbol.
     */
    public static function fromClass(ReflectionClass $class)
    {
        return new self(\explode('\\', $class->getName()), true);
    }

    /**
     * Get the function name of the supplied function reflector.
     *
     * @api
     *
     * @param ReflectionFunction $function The function reflector.
     *
     * @return SymbolInterface The newly created symbol.
     */
    public static function fromFunction(ReflectionFunction $function)
    {
        return new self(\explode('\\', $function->getName()), true);
    }

    /**
     * Construct a new symbol.
     *
     * @param array<string> $atoms       The atoms.
     * @param boolean       $isQualified True if qualified.
     */
    public function __construct(array $atoms, $isQualified)
    {
        if (\count($atoms) < 1) {
            throw new InvalidArgumentException('Symbols cannot be empty.');
        }

        $this->atoms = $atoms;
        $this->isQualified = $isQualified;
    }

    /**
     * Get the atoms.
     *
     * @api
     *
     * @return array<string> $atoms The atoms.
     */
    public function atoms()
    {
        return $this->atoms;
    }

    /**
     * Returns true if qualified.
     *
     * @api
     *
     * @return boolean True if qualified.
     */
    public function isQualified()
    {
        return $this->isQualified;
    }

    /**
     * Get the runtime string representation of this symbol.
     *
     * @api
     *
     * @return string The runtime string representation.
     */
    public function runtimeString()
    {
        return \implode('\\', $this->atoms);
    }

    /**
     * Get the string representation of this symbol.
     *
     * @api
     *
     * @return string The string representation.
     */
    public function __toString()
    {
        if ($this->isQualified) {
            return '\\' . \implode('\\', $this->atoms);
        }

        return \implode('\\', $this->atoms);
    }

    private $atoms;
    private $isQualified;
}
