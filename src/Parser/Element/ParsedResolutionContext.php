<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Parser\Element;

use Eloquent\Cosmos\Resolution\Context\ResolutionContext;

/**
 * Augments a resolution context with parser data.
 */
class ParsedResolutionContext extends ResolutionContext
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
     * The byte offset.
     *
     * @var integer
     */
    public $offset;

    /**
     * The byte size.
     *
     * @var integer
     */
    public $size;

    /**
     * The token offset.
     *
     * @var integer
     */
    public $tokenOffset;

    /**
     * The token size.
     *
     * @var integer
     */
    public $tokenSize;

    /**
     * The symbols defined under this context.
     *
     * @var array<ParsedSymbol>
     */
    public $symbols;
}
