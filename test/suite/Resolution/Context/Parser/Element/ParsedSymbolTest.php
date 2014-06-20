<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Context\Parser\Element;

use Eloquent\Cosmos\Resolution\Context\Parser\ParserPosition;
use Eloquent\Cosmos\Symbol\Symbol;
use Eloquent\Cosmos\Symbol\SymbolType;
use PHPUnit_Framework_TestCase;

/**
 * @covers \Eloquent\Cosmos\Resolution\Context\Parser\Element\ParsedSymbol
 * @covers \Eloquent\Cosmos\Resolution\Context\Parser\Element\AbstractParsedElement
 */
class ParsedSymbolTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->innerSymbol = Symbol::fromString('\Foo');
        $this->position = new ParserPosition(111, 222);
        $this->symbol = new ParsedSymbol($this->innerSymbol, SymbolType::CONSTANT(), $this->position, 333, 444);
    }

    public function testConstructor()
    {
        $this->assertSame($this->innerSymbol, $this->symbol->symbol());
        $this->assertSame(SymbolType::CONSTANT(), $this->symbol->type());
        $this->assertSame($this->position, $this->symbol->position());
        $this->assertSame(333, $this->symbol->offset());
        $this->assertSame(444, $this->symbol->size());
    }

    public function testConstructorDefaults()
    {
        $this->symbol = new ParsedSymbol($this->innerSymbol);

        $this->assertSame(SymbolType::CLA55(), $this->symbol->type());
        $this->assertEquals(new ParserPosition(0, 0), $this->symbol->position());
        $this->assertSame(0, $this->symbol->offset());
        $this->assertSame(0, $this->symbol->size());
    }
}
