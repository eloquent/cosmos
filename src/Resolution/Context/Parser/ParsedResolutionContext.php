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

use Eloquent\Cosmos\Resolution\Context\ResolutionContext;
use Eloquent\Cosmos\Resolution\Context\ResolutionContextInterface;

/**
 * Represents a parsed resolution context and its related symbols.
 */
class ParsedResolutionContext extends AbstractParsedElement implements
    ParsedResolutionContextInterface
{
    /**
     * Construct a new parsed resolution context.
     *
     * @param ResolutionContextInterface|null   $context  The resolution context.
     * @param array<ParsedSymbolInterface>|null $symbols  The symbols defined under the parsed resolution context.
     * @param ParserPositionInterface|null      $position The position.
     */
    public function __construct(
        ResolutionContextInterface $context = null,
        array $symbols = null,
        ParserPositionInterface $position = null
    ) {
        if (null === $context) {
            $context = new ResolutionContext;
        }
        if (null === $symbols) {
            $symbols = array();
        }

        parent::__construct($position);

        $this->context = $context;
        $this->symbols = $symbols;
    }

    /**
     * Get the resolution context.
     *
     * @return ResolutionContextInterface The resolution context.
     */
    public function context()
    {
        return $this->context;
    }

    /**
     * Get the symbols defined under the parsed resolution context.
     *
     * @return array<ParsedSymbolInterface> The defined symbols.
     */
    public function symbols()
    {
        return $this->symbols;
    }

    private $context;
    private $symbols;
}
