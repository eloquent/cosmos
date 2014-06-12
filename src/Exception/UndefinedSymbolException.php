<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Exception;

use Eloquent\Cosmos\Symbol\SymbolInterface;
use Exception;

/**
 * The specified symbol is undefined.
 */
final class UndefinedSymbolException extends Exception
{
    /**
     * Construct a new undefined symbol exception.
     *
     * @param SymbolInterface $symbol The symbol.
     * @param Exception|null  $cause  The cause, if available.
     */
    public function __construct(
        SymbolInterface $symbol,
        Exception $cause = null
    ) {
        $this->symbol = $symbol;

        parent::__construct(
            sprintf(
                'Undefined symbol %s.',
                var_export($symbol->string(), true)
            ),
            0,
            $cause
        );
    }

    /**
     * Get the undefined symbol.
     *
     * @return SymbolInterface The symbol.
     */
    public function symbol()
    {
        return $this->symbol;
    }

    private $symbol;
}
