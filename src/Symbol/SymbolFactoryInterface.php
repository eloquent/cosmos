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
 * The interface implemented by symbol factories.
 *
 * @api
 */
interface SymbolFactoryInterface
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
    public function createFromString($symbol);

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
    public function createFromRuntimeString($symbol);

    /**
     * Create a new symbol from a set of symbol atoms.
     *
     * Unless otherwise specified, created symbols will be qualified.
     *
     * @api
     *
     * @param array<string> $atoms       The symbol atoms.
     * @param boolean|null  $isQualified True if qualified.
     *
     * @return SymbolInterface            The newly created symbol.
     * @throws InvalidSymbolAtomException If any of the supplied atoms are invalid.
     */
    public function createFromAtoms(array $atoms, $isQualified = null);
}
