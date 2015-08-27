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

use Eloquent\Cosmos\Resolution\Context\ResolutionContextElementInterface;
use Eloquent\Pathogen\PathInterface;

/**
 * The common interface implemented by qualified symbols and symbol references.
 */
interface SymbolInterface extends
    PathInterface,
    ResolutionContextElementInterface
{
    /**
     * Get the first atom of this symbol as a symbol reference.
     *
     * If this symbol is already a short symbol reference, it will be returned
     * unaltered.
     *
     * @return SymbolReferenceInterface The short symbol.
     */
    public function firstAtomAsReference();

    /**
     * Get the last atom of this symbol as a symbol reference.
     *
     * If this symbol is already a short symbol reference, it will be returned
     * unaltered.
     *
     * @return SymbolReferenceInterface The short symbol.
     */
    public function lastAtomAsReference();
}
