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

use PHPUnit_Framework_TestCase;

class ParserPositionTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $this->position = new ParserPosition(111, 222);

        $this->assertSame(111, $this->position->line());
        $this->assertSame(222, $this->position->column());
    }
}
