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
use Eloquent\Cosmos\Symbol\QualifiedSymbolInterface;

/**
 * Represents a parsed resolution context and its related symbols.
 */
class ParsedResolutionContext implements ParsedResolutionContextInterface
{
    /**
     * Construct a new parsed resolution context.
     *
     * @param ResolutionContextInterface|null      $context The resolution context.
     * @param array<QualifiedSymbolInterface>|null $symbols The symbols defined under the parsed resolution context.
     */
    public function __construct(
        ResolutionContextInterface $context = null,
        array $symbols = null
    ) {
        if (null === $context) {
            $context = new ResolutionContext;
        }
        if (null === $symbols) {
            $symbols = array();
        }

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
     * @return array<QualifiedSymbolInterface> The defined symbols.
     */
    public function symbols()
    {
        return $this->symbols;
    }

    private $context;
    private $symbols;
}
