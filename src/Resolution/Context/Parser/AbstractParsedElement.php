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

/**
 * An abstract base class for implementing parsed elements.
 */
abstract class AbstractParsedElement implements ParsedElementInterface
{
    /**
     * Construct a new parsed element.
     *
     * @param ParserPositionInterface|null $position The position.
     */
    public function __construct($position = null)
    {
        if (null === $position) {
            $position = new ParserPosition(0, 0);
        }

        $this->position = $position;
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

    private $position;
}
