<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Symbol;

use Eloquent\Cosmos\Resolution\Context\ResolutionContextInterface;
use Eloquent\Pathogen\AbsolutePathInterface;

/**
 * The interface implemented by fully qualified symbols.
 */
interface QualifiedSymbolInterface extends
    AbsolutePathInterface,
    SymbolInterface
{
    /**
     * Find the shortest symbol that will resolve to this symbol from within the
     * supplied resolution context.
     *
     * If this symbol is not a child of the primary namespace, and there are no
     * related use statements, this method will return a qualified symbol.
     *
     * @param ResolutionContextInterface $context The resolution context.
     *
     * @return SymbolInterface The shortest symbol.
     */
    public function relativeToContext(ResolutionContextInterface $context);
}
