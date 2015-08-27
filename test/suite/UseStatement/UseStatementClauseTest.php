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

use Eloquent\Cosmos\Symbol\Symbol;
use PHPUnit_Framework_TestCase;

class UseStatementClauseTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->symbol = Symbol::fromString('\Namespace\Symbol');
        $this->alias = 'Alias';
        $this->clause = new UseStatementClause($this->symbol, $this->alias);
    }

    public function testConstructor()
    {
        $this->assertSame($this->symbol, $this->clause->symbol());
        $this->assertSame($this->alias, $this->clause->alias());
        $this->assertSame($this->alias, $this->clause->effectiveAlias());
    }

    public function testConstructorDefaults()
    {
        $this->clause = new UseStatementClause($this->symbol);

        $this->assertNull($this->clause->alias());
        $this->assertSame('Symbol', $this->clause->effectiveAlias());
    }

    public function testToString()
    {
        $this->assertSame('Namespace\Symbol as Alias', strval($this->clause));
    }

    public function testToStringNoAlias()
    {
        $this->clause = new UseStatementClause($this->symbol);

        $this->assertSame('Namespace\Symbol', strval($this->clause));
    }
}
