<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Symbol\Factory;

use Eloquent\Cosmos\Exception\InvalidSymbolAtomException;
use Eloquent\Cosmos\Symbol\Symbol;
use Eloquent\Cosmos\Symbol\SymbolInterface;

/**
 * Creates symbol instances.
 */
class SymbolFactory implements SymbolFactoryInterface
{
    /**
     * Get a static instance of this factory.
     *
     * @return SymbolFactoryInterface The static factory.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Create a new symbol from its string representation.
     *
     * @param string $symbol The string representation.
     *
     * @return SymbolInterface            The newly created symbol.
     * @throws InvalidSymbolAtomException If the supplied string is invalid.
     */
    public function createFromString($symbol)
    {
        $atoms = explode('\\', $symbol);
        $isQualified = false;

        if (count($atoms) > 1 && '' === $atoms[0]) {
            array_shift($atoms);
            $isQualified = true;
        }

        foreach ($atoms as $atom) {
            if (!preg_match(self::$atomPattern, $atom)) {
                throw new InvalidSymbolAtomException($atom);
            }
        }

        return new Symbol($atoms, $isQualified);
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
    public function createFromRuntimeString($symbol)
    {
        $atoms = explode('\\', $symbol);

        if (count($atoms) > 1 && '' === $atoms[0]) {
            array_shift($atoms);
        }

        foreach ($atoms as $atom) {
            if (!preg_match(self::$atomPattern, $atom)) {
                throw new InvalidSymbolAtomException($atom);
            }
        }

        return new Symbol($atoms, true);
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
    public function createFromAtoms(array $atoms, $isQualified = null)
    {
        foreach ($atoms as $atom) {
            if (!preg_match(self::$atomPattern, $atom)) {
                throw new InvalidSymbolAtomException($atom);
            }
        }

        if (null === $isQualified) {
            $isQualified = true;
        }

        return new Symbol($atoms, $isQualified);
    }

    private static $instance;
    private static $atomPattern =
        '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]+$/S';
}
