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
 * Represents a symbol.
 */
class Symbol implements SymbolInterface
{
    /**
     * Create a new symbol from its string representation.
     *
     * @param string $string The string.
     *
     * @return SymbolInterface The newly created symbol.
     */
    public static function fromString($string)
    {
        return new self();
    }

    /**
     * Get the string representation of this symbol.
     *
     * @return string The string representation.
     */
    public function __toString()
    {
        return '';
    }
}
