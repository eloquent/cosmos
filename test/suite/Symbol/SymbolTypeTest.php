<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Symbol;

use Eloquent\Liberator\Liberator;
use PHPUnit_Framework_TestCase;

class SymbolTypeTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        Liberator::liberateClass('Eloquent\Cosmos\Symbol\SymbolType')->members = array();
    }

    public function testIsType()
    {
        $this->assertTrue(SymbolType::CLA55()->isType());
        $this->assertTrue(SymbolType::INTERF4CE()->isType());
        $this->assertTrue(SymbolType::TRA1T()->isType());
        $this->assertFalse(SymbolType::FUNCT1ON()->isType());
        $this->assertFalse(SymbolType::CONSTANT()->isType());
    }
}
