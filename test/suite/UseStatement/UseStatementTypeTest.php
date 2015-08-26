<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\UseStatement;

use Eloquent\Cosmos\Symbol\SymbolType;
use Eloquent\Liberator\Liberator;
use PHPUnit_Framework_TestCase;

class UseStatementTypeTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        Liberator::liberateClass('Eloquent\Cosmos\UseStatement\UseStatementType')->members = array();
    }

    public function testMemberBySymbolType()
    {
        $this->assertSame(UseStatementType::TYPE(), UseStatementType::memberBySymbolType(SymbolType::CLA55()));
        $this->assertSame(UseStatementType::TYPE(), UseStatementType::memberBySymbolType(SymbolType::INTERF4CE()));
        $this->assertSame(UseStatementType::TYPE(), UseStatementType::memberBySymbolType(SymbolType::TRA1T()));
        $this->assertSame(UseStatementType::FUNCT1ON(), UseStatementType::memberBySymbolType(SymbolType::FUNCT1ON()));
        $this->assertSame(UseStatementType::CONSTANT(), UseStatementType::memberBySymbolType(SymbolType::CONSTANT()));
    }
}
