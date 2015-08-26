<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\UseStatement;

use Eloquent\Cosmos\Symbol\SymbolType;
use Eloquent\Enumeration\AbstractEnumeration;

/**
 * An enumeration of available use statement types.
 */
final class UseStatementType extends AbstractEnumeration
{
    /**
     * A regular use statement, used for importing classes, interfaces, and traits.
     */
    const TYPE = 'type';

    /**
     * A function use statement.
     */
    const FUNCT1ON = 'function';

    /**
     * A constant use statement.
     */
    const CONSTANT = 'const';

    /**
     * Get the relevant use statement type for a symbol type.
     *
     * @param SymbolType $symbolType The symbol type.
     *
     * @return UseStatementType The relevant use statement type.
     */
    public static function memberBySymbolType(SymbolType $symbolType)
    {
        if ($symbolType->isType()) {
            return self::TYPE();
        }

        return self::memberByValue($symbolType->value());
    }
}
