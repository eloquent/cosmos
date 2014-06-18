<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Context\Parser;

use Eloquent\Cosmos\Symbol\QualifiedSymbolInterface;
use Eloquent\Cosmos\Symbol\SymbolType;

/**
 * Represents a parsed symbol and its symbol type.
 */
class ParsedSymbol extends AbstractParsedElement implements
    ParsedSymbolInterface
{
    /**
     * Construct a new parsed symbol.
     *
     * @param QualifiedSymbolInterface     $symbol   The symbol.
     * @param SymbolType|null              $type     The symbol type.
     * @param ParserPositionInterface|null $position The position.
     */
    public function __construct(
        QualifiedSymbolInterface $symbol,
        SymbolType $type = null,
        ParserPositionInterface $position = null
    ) {
        if (null === $type) {
            $type = SymbolType::CLA55();
        }

        parent::__construct($position);

        $this->symbol = $symbol;
        $this->type = $type;
    }

    /**
     * Get the symbol.
     *
     * @return QualifiedSymbolInterface The symbol.
     */
    public function symbol()
    {
        return $this->symbol;
    }

    /**
     * Get the symbol type.
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