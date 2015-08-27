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
use Eloquent\Cosmos\Symbol\Factory\SymbolFactory;

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

        if ($isQualified) {
            $this->string = '\\' . implode('\\', $atoms);
        } else {
            $this->string = implode('\\', $atoms);
        }
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
     * Returns true if qualified.
     *
     * @return boolean True if qualified.
     */
    public function isQualified()
    {
        return $this->isQualified;
    }

    /**
     * Get the string representation of this symbol.
     *
     * @return string The string representation.
     */
    public function __toString()
    {
        return $this->string;
    }

    private $atoms;
    private $isQualified;
    private $string;
}
