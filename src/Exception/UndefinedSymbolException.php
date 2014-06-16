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
use Eloquent\Cosmos\Symbol\SymbolType;
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
     * @param SymbolType      $type   The symbol type.
     * @param Exception|null  $cause  The cause, if available.
     */
    public function __construct(
        SymbolInterface $symbol,
        SymbolType $type,
        Exception $cause = null
    ) {
        $this->symbol = $symbol;
        $this->type = $type;

        parent::__construct(
            sprintf(
                'Undefined %s %s.',
                $type->value(),
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

    /**
     * Get the undefined symbol type.
     *
     * @return SymbolType The type.
     */
    public function type()
    {
        return $this->type;
    }

    private $symbol;
    private $type;
}
