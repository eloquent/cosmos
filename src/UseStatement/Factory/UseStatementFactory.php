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
use Eloquent\Cosmos\UseStatement\UseStatement;
use Eloquent\Cosmos\UseStatement\UseStatementType;

/**
 * Creates use statement instances.
 */
class UseStatementFactory implements UseStatementFactoryInterface
{
    /**
     * Get a static instance of this factory.
     *
     * @return UseStatementFactoryInterface The static factory.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Create a new use statement.
     *
     * @param QualifiedSymbolInterface      $symbol The symbol.
     * @param SymbolReferenceInterface|null $alias  The alias for the symbol.
     * @param UseStatementType|null         $type   The use statement type.
     *
     * @throws InvalidSymbolAtomException If an invalid alias is supplied.
     */
    public function create(
        QualifiedSymbolInterface $symbol,
        SymbolReferenceInterface $alias = null,
        UseStatementType $type = null
    ) {
        return new UseStatement($symbol, $alias, $type);
    }

    private static $instance;
}
