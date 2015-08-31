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
use Eloquent\Cosmos\Symbol\SymbolInterface;

/**
 * The interface implemented by symbol resolvers.
 *
 * @api
 */
interface SymbolResolverInterface
{
    /**
     * Resolve a symbol against a resolution context.
     *
     * Symbols that are already qualified will be returned unaltered.
     *
     * @api
     *
     * @param ResolutionContextInterface $context The resolution context.
     * @param SymbolInterface            $symbol  The symbol to resolve.
     * @param string|null                $type    The symbol type.
     *
     * @return SymbolInterface The resolved, qualified symbol.
     */
    public function resolve(
        ResolutionContextInterface $context,
        SymbolInterface $symbol,
        $type = null
    );
}
