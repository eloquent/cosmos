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
 * The interface implemented by parsed elements.
 */
interface ParsedElementInterface
{
    /**
     * Get the line number.
     *
     * @return integer The line number.
     */
    public function lineNumber();

    /**
     * Get the column number.
     *
     * @return integer The column number.
     */
    public function columnNumber();
}
