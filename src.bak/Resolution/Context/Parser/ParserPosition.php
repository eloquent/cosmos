<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Context\Parser;

/**
 * Represents the position at which an element was parsed.
 */
class ParserPosition implements ParserPositionInterface
{
    /**
     * Construct a new parser position.
     *
     * @param integer $line   The line number.
     * @param integer $column The column number.
     */
    public function __construct($line, $column)
    {
        $this->line = $line;
        $this->column = $column;
    }

    /**
     * Get the line number.
     *
     * @return integer the line number.
     */
    public function line()
    {
        return $this->line;
    }

    /**
     * Get the column number.
     *
     * @return integer the column number.
     */
    public function column()
    {
        return $this->column;
    }

    private $line;
    private $column;
}
