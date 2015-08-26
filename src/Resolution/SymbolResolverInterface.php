<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution;

use Eloquent\Cosmos\Resolution\Context\ResolutionContextInterface;
use Eloquent\Cosmos\Symbol\QualifiedSymbolInterface;
use Eloquent\Cosmos\Symbol\SymbolInterface;
use Eloquent\Cosmos\Symbol\SymbolType;
use Eloquent\Pathogen\Resolver\BasePathResolverInterface;

/**
 * The interface implemented by symbol resolvers.
 */
interface SymbolResolverInterface extends BasePathResolverInterface
{
    /**
     * Resolve a symbol of a specified type against the supplied namespace.
     *
     * This method assumes no use statements are defined.
     *
     * @param QualifiedSymbolInterface $primaryNamespace The namespace.
     * @param SymbolInterface          $symbol           The symbol to resolve.
     * @param SymbolType               $type             The symbol type.
     *
     * @return QualifiedSymbolInterface The resolved, qualified symbol.
     */
    public function resolveAsType(
        QualifiedSymbolInterface $primaryNamespace,
        SymbolInterface $symbol,
        SymbolType $type
    );

    /**
     * Resolve a symbol against the supplied resolution context.
     *
     * Symbols that are already qualified will be returned unaltered.
     *
     * @param ResolutionContextInterface $context The resolution context.
     * @param SymbolInterface            $symbol  The symbol to resolve.
     * @param SymbolType|null            $type    The symbol type.
     *
     * @return QualifiedSymbolInterface The resolved, qualified symbol.
     */
    public function resolveAgainstContext(
        ResolutionContextInterface $context,
        SymbolInterface $symbol,
        SymbolType $type = null
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
     * @param SymbolType|null            $type    The symbol type.
     *
     * @return SymbolInterface The shortest symbol.
     */
    public function relativeToContext(
        ResolutionContextInterface $context,
        QualifiedSymbolInterface $symbol,
        SymbolType $type = null
    );
}
