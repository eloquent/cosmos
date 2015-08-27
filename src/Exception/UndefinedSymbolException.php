<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2015 Erin Millard
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
     * @param string          $type   The symbol type.
     * @param SymbolInterface $symbol The symbol.
     * @param Exception|null  $cause  The cause, if available.
     */
    public function __construct(
        $type,
        SymbolInterface $symbol,
        Exception $cause = null
    ) {
        $this->symbol = $symbol;
        $this->type = $type;

        parent::__construct(
            \sprintf(
                'Undefined %s %s.',
                $type,
                \var_export(\strval($symbol), true)
            ),
            0,
            $cause
        );
    }

    /**
     * Get the undefined symbol type.
     *
     * @return string The type.
     */
    public function type()
    {
        return $this->type;
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
    private $type;
}
