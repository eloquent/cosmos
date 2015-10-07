<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Cache;

use PHPUnit_Framework_TestCase;

/**
 * @covers \Eloquent\Cosmos\Cache\MemoryCache
 */
class MemoryCacheTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->subject = new MemoryCache();
    }

    public function testCache()
    {
        $this->assertNull($this->subject->get('a.b.c'));

        $this->subject->set('a.b.c', 'x');

        $this->assertSame('x', $this->subject->get('a.b.c'));

        $this->subject->set('a.b.c', null);

        $this->assertNull($this->subject->get('a.b.c'));
    }
}
