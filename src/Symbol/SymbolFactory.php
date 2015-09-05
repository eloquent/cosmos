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
 * Creates symbol instances.
 *
 * @api
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
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

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
    public function createFromString($symbol)
    {
        $atoms = \explode('\\', $symbol);
        $isQualified = false;

        if (\count($atoms) > 1 && '' === $atoms[0]) {
            \array_shift($atoms);
            $isQualified = true;
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
     * @api
     *
     * @param string $symbol The string representation.
     *
     * @return SymbolInterface            The newly created symbol.
     * @throws InvalidSymbolAtomException If the supplied string is invalid.
     */
    public function createFromRuntimeString($symbol)
    {
        $atoms = \explode('\\', $symbol);

        if (\count($atoms) > 1 && '' === $atoms[0]) {
            \array_shift($atoms);
        }

        return new Symbol($atoms, true);
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
    public function createFromAtoms(array $atoms, $isQualified = true)
    {
        return new Symbol($atoms, $isQualified);
    }

    private static $instance;
}
