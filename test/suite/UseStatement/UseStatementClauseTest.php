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

/**
 * @covers \Eloquent\Cosmos\UseStatement\UseStatementClause
 */
class UseStatementClauseTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->symbol = Symbol::fromString('\Namespace\Symbol');
        $this->alias = 'Alias';
        $this->subject = new UseStatementClause($this->symbol, $this->alias);
    }

    public function testConstructor()
    {
        $this->assertSame($this->symbol, $this->subject->symbol());
        $this->assertSame($this->alias, $this->subject->alias());
        $this->assertSame($this->alias, $this->subject->effectiveAlias());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new UseStatementClause($this->symbol);

        $this->assertNull($this->subject->alias());
        $this->assertSame('Symbol', $this->subject->effectiveAlias());
    }

    public function testToString()
    {
        $this->assertSame('Namespace\Symbol as Alias', strval($this->subject));
    }

    public function testToStringNoAlias()
    {
        $this->subject = new UseStatementClause($this->symbol);

        $this->assertSame('Namespace\Symbol', strval($this->subject));
    }
}
