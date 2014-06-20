<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
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
     * Get the character offset for the start of the namespace symbol.
     *
     * @return integer|null The namespace symbol offset, or null if there is no namespace symbol.
     */
    public function namespaceSymbolOffset();

    /**
     * Get the size of the parsed namespace symbol.
     *
     * @return integer|null The namespace symbol size in bytes, or null if there is no namespace symbol.
     */
    public function namespaceSymbolSize();

    /**
     * Get the character offset for the start of the namespace body.
     *
     * @return integer The namespace body offset.
     */
    public function namespaceBodyOffset();
}
