<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Context\Parser\Element;

use Eloquent\Cosmos\Symbol\QualifiedSymbolInterface;
use Eloquent\Cosmos\Symbol\SymbolType;

/**
 * The interface implemented by parsed symbols.
 */
interface ParsedSymbolInterface extends ParsedElementInterface
{
    /**
     * Get the symbol.
     *
     * @return QualifiedSymbolInterface The symbol.
     */
    public function symbol();

    /**
     * Get the symbol type.
     *
     * @return SymbolType The type.
     */
    public function type();
}
