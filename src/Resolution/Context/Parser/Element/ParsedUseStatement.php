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
     * The line number, if parsed.
     *
     * @var integer|null
     */
    public $line;

    /**
     * The column number, if parsed.
     *
     * @var integer|null
     */
    public $column;

    /**
     * The offset, if parsed.
     *
     * @var integer|null
     */
    public $offset;

    /**
     * The size, if parsed.
     *
     * @var integer|null
     */
    public $size;
}
