<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution;

use Eloquent\Cosmos\Resolution\Context\ResolutionContextInterface;
use Eloquent\Cosmos\Symbol\QualifiedSymbolInterface;
use Eloquent\Cosmos\Symbol\SymbolInterface;
use Eloquent\Pathogen\Resolver\BasePathResolverInterface;

/**
 * The interface implemented by symbol resolvers.
 */
interface SymbolResolverInterface extends BasePathResolverInterface
{
    /**
     * Resolve a symbol against the supplied resolution context.
     *
     * Symbols that are already qualified will be returned unaltered.
     *
     * @param ResolutionContextInterface $context The resolution context.
     * @param SymbolInterface            $symbol  The symbol to resolve.
     *
     * @return QualifiedSymbolInterface The resolved, qualified symbol.
     */
    public function resolveAgainstContext(
        ResolutionContextInterface $context,
        SymbolInterface $symbol
    );

    /**
     * Find the shortest symbol that will resolve to the supplied qualified
     * symbol from within the supplied resolution context.
     *
     * If the qualified symbol is not a child of the primary namespace, and
     * there are no related use statements, this method will return a qualified
     * symbol.
     *
     * @param ResolutionContextInterface $context The resolution context.
     * @param QualifiedSymbolInterface   $symbol  The symbol to resolve.
     *
     * @return SymbolInterface The shortest symbol.
     */
    public function relativeToContext(
        ResolutionContextInterface $context,
        QualifiedSymbolInterface $symbol
    );
}
