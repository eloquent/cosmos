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
     * @param integer|null $lineNumber   The line number.
     * @param integer|null $columnNumber The column number.
     */
    public function __construct($lineNumber = null, $columnNumber = null)
    {
        if (null === $lineNumber) {
            $lineNumber = 0;
        }
        if (null === $columnNumber) {
            $columnNumber = 0;
        }

        $this->lineNumber = $lineNumber;
        $this->columnNumber = $columnNumber;
    }

    /**
     * Get the line number.
     *
     * @return integer The line number.
     */
    public function lineNumber()
    {
        return $this->lineNumber;
    }

    /**
     * Get the column number.
     *
     * @return integer The column number.
     */
    public function columnNumber()
    {
        return $this->columnNumber;
    }

    private $lineNumber;
    private $columnNumber;
}
