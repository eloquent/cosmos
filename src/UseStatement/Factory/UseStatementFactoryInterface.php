<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\UseStatement\Factory;

use Eloquent\Cosmos\Symbol\Exception\InvalidSymbolAtomException;
use Eloquent\Cosmos\Symbol\QualifiedSymbolInterface;
use Eloquent\Cosmos\Symbol\SymbolReferenceInterface;

/**
 * The interface implemented by use statement factories.
 */
interface UseStatementFactoryInterface
{
    /**
     * Create a new use statement.
     *
     * @param QualifiedSymbolInterface      $symbol The symbol.
     * @param SymbolReferenceInterface|null $alias  The alias for the symbol.
     *
     * @throws InvalidSymbolAtomException If an invalid alias is supplied.
     */
    public function create(
        QualifiedSymbolInterface $symbol,
        SymbolReferenceInterface $alias = null
    );
}
