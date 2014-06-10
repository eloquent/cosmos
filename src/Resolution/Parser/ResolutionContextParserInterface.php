<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Parser;

/**
 * The interface implemented by resolution context parsers.
 */
interface ResolutionContextParserInterface
{
    /**
     * Parse all resolution contexts from the supplied source code.
     *
     * @param string $source The source code to parse.
     *
     * @return array<ParsedResolutionContextInterface> The parsed resolution contexts.
     */
    public function parseSource($source);
}
