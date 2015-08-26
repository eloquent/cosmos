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
 * The interface implemented by parser positions.
 */
interface ParserPositionInterface
{
    /**
     * Get the line number.
     *
     * @return integer the line number.
     */
    public function line();

    /**
     * Get the column number.
     *
     * @return integer the column number.
     */
    public function column();
}
