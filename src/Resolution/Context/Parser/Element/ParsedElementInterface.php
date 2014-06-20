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

/**
 * The interface implemented by parsed elements.
 */
interface ParsedElementInterface
{
    /**
     * Get the position.
     *
     * @return ParserPositionInterface The position.
     */
    public function position();

    /**
     * Get the character offset for the start of the element.
     *
     * @return integer The start offset.
     */
    public function startOffset();

    /**
     * Get the size of the parsed element.
     *
     * @return integer The element size in bytes.
     */
    public function size();
}
