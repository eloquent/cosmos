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

use Eloquent\Cosmos\Resolution\Context\Parser\ParserPosition;
use Eloquent\Cosmos\Resolution\Context\Parser\ParserPositionInterface;

/**
 * An abstract base class for implementing parsed elements.
 */
abstract class AbstractParsedElement implements ParsedElementInterface
{
    /**
     * Construct a new parsed element.
     *
     * @param ParserPositionInterface|null $position    The position.
     * @param integer|null                 $startOffset The start offset.
     * @param integer|null                 $size        The element size in bytes.
     */
    public function __construct(
        $position = null,
        $startOffset = null,
        $size = null
    ) {
        if (null === $position) {
            $position = new ParserPosition(0, 0);
        }
        if (null === $startOffset) {
            $startOffset = 0;
        }
        if (null === $size) {
            $size = 0;
        }

        $this->position = $position;
        $this->startOffset = $startOffset;
        $this->size = $size;
    }

    /**
     * Get the position.
     *
     * @return ParserPositionInterface The position.
     */
    public function position()
    {
        return $this->position;
    }

    /**
     * Get the character offset for the start of the element.
     *
     * @return integer The start offset.
     */
    public function startOffset()
    {
        return $this->startOffset;
    }

    /**
     * Get the size of the parsed element.
     *
     * @return integer The element size in bytes.
     */
    public function size()
    {
        return $this->size;
    }

    private $position;
    private $startOffset;
    private $size;
}
