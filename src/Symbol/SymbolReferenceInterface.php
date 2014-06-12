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
use Eloquent\Pathogen\RelativePathInterface;

/**
 * The interface implemented by symbol references.
 */
interface SymbolReferenceInterface extends
    RelativePathInterface,
    SymbolInterface
{
    /**
     * Resolve this symbol against the supplied resolution context.
     *
     * @param ResolutionContextInterface $context The resolution context.
     *
     * @return QualifiedSymbolInterface The resolved, qualified symbol.
     */
    public function resolveAgainstContext(ResolutionContextInterface $context);
}
