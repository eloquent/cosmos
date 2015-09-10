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

/**
 * The interface implemented by symbols.
 *
 * @api
 */
interface SymbolInterface
{
    /**
     * Get the atoms.
     *
     * @api
     *
     * @return array<string> $atoms The atoms.
     */
    public function atoms();

    /**
     * Returns true if qualified.
     *
     * @api
     *
     * @return boolean True if qualified.
     */
    public function isQualified();

    /**
     * Get the runtime string representation of this symbol.
     *
     * @api
     *
     * @return string The runtime string representation.
     */
    public function runtimeString();

    /**
     * Get the string representation of this symbol.
     *
     * @api
     *
     * @return string The string representation.
     */
    public function __toString();
}
