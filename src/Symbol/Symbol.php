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

/**
 * Represents a symbol.
 */
class Symbol implements SymbolInterface
{
    /**
     * Create a new symbol from its string representation.
     *
     * @param string $symbol The string representation.
     *
     * @return SymbolInterface            The newly created symbol.
     * @throws InvalidSymbolAtomException If the supplied string is invalid.
     */
    public static function fromString($symbol)
    {
        return SymbolFactory::instance()->createFromString($symbol);
    }

    /**
     * Create a new symbol from its string representation, but always return a
     * qualified symbol.
     *
     * This method emulates the manner in which symbols are typically
     * interpreted at run time.
     *
     * @param string $symbol The string representation.
     *
     * @return SymbolInterface            The newly created symbol.
     * @throws InvalidSymbolAtomException If the supplied string is invalid.
     */
    public static function fromRuntimeString($symbol)
    {
        return SymbolFactory::instance()->createFromRuntimeString($symbol);
    }

    /**
     * Create a new symbol from a set of atoms.
     *
     * Unless otherwise specified, created symbols will be qualified.
     *
     * @param array<string> $atoms       The atoms.
     * @param boolean|null  $isQualified True if qualified.
     *
     * @return SymbolInterface            The newly created symbol.
     * @throws InvalidSymbolAtomException If any of the supplied atoms are invalid.
     */
    public static function fromAtoms(array $atoms, $isQualified = null)
    {
        return SymbolFactory::instance()->createFromAtoms($atoms, $isQualified);
    }

    /**
     * Construct a new symbol.
     *
     * @param array<string> $atoms       The atoms.
     * @param boolean|null  $isQualified True if qualified.
     */
    public function __construct(array $atoms, $isQualified)
    {
        $this->atoms = $atoms;
        $this->isQualified = $isQualified;
    }

    /**
     * Get the atoms.
     *
     * @return array<string> $atoms The atoms.
     */
    public function atoms()
    {
        return $this->atoms;
    }

    /**
     * Get the first atom.
     *
     * @return string The atom.
     */
    public function firstAtom()
    {
        return $this->atoms[0];
    }

    /**
     * Returns true if qualified.
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
     * @return string The runtime string representation.
     */
    public function runtimeString()
    {
        return implode('\\', $this->atoms);
    }

    /**
     * Get the string representation of this symbol.
     *
     * @return string The string representation.
     */
    public function __toString()
    {
        if ($this->isQualified) {
            return '\\' . implode('\\', $this->atoms);
        }

        return implode('\\', $this->atoms);
    }

    private $atoms;
    private $isQualified;
}
