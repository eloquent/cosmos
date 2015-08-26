<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Context\Parser\Element;

use Eloquent\Cosmos\Resolution\Context\ResolutionContextInterface;

/**
 * The interface implemented by parsed resolution contexts.
 */
interface ParsedResolutionContextInterface extends
    ResolutionContextInterface,
    ParsedElementInterface
{
    /**
     * Get the resolution context.
     *
     * @return ResolutionContextInterface The resolution context.
     */
    public function context();

    /**
     * Get the symbols defined under the parsed resolution context.
     *
     * @return array<ParsedSymbolInterface> The defined symbols.
     */
    public function symbols();

    /**
     * Get the source code tokens contained in this resolution context.
     *
     * @return array<tuple<integer|string,string,integer,integer>> The tokens.
     */
    public function tokens();
}
