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

use Eloquent\Cosmos\Symbol\Symbol;
use Eloquent\Cosmos\Symbol\SymbolType;
use PHPUnit_Framework_TestCase;

/**
 * @covers \Eloquent\Cosmos\Resolution\Context\Parser\ParsedSymbol
 * @covers \Eloquent\Cosmos\Resolution\Context\Parser\AbstractParsedElement
 */
class ParsedSymbolTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->symbol = Symbol::fromString('\Foo');
        $this->position = new ParserPosition(111, 222);
        $this->parsedSymbol = new ParsedSymbol($this->symbol, SymbolType::CONSTANT(), $this->position);
    }

    public function testConstructor()
    {
        $this->assertSame($this->symbol, $this->parsedSymbol->symbol());
        $this->assertSame(SymbolType::CONSTANT(), $this->parsedSymbol->type());
        $this->assertSame($this->position, $this->parsedSymbol->position());
    }

    public function testConstructorDefaults()
    {
        $this->parsedSymbol = new ParsedSymbol($this->symbol);

        $this->assertSame(SymbolType::CLA55(), $this->parsedSymbol->type());
        $this->assertEquals(new ParserPosition(0, 0), $this->parsedSymbol->position());
    }
}
