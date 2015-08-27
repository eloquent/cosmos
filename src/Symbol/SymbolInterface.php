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
 * The common interface implemented by qualified symbols and symbol references.
 */
interface SymbolInterface
{
    /**
     * Get the runtime string representation of this symbol.
     *
     * @return string The runtime string representation.
     */
    public function runtimeString();

    /**
     * Get the string representation of this symbol.
     *
     * @return string The string representation.
     */
    public function __toString();
}
