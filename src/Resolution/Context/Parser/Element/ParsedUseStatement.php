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

use Eloquent\Cosmos\UseStatement\UseStatement;

/**
 * Augments a use statement with parser data.
 */
class ParsedUseStatement extends UseStatement
{
    /**
     * The line number.
     *
     * @var integer
     */
    public $line;

    /**
     * The column number.
     *
     * @var integer
     */
    public $column;

    /**
     * The offset.
     *
     * @var integer
     */
    public $offset;

    /**
     * The size.
     *
     * @var integer
     */
    public $size;
}
