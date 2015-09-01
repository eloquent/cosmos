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

    /**
     * The tokens.
     *
     * @var array<tuple<integer|string,string,integer,integer,integer,integer>>
     */
    public $tokens;
}