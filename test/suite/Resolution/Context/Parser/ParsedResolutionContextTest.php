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

use Eloquent\Cosmos\Resolution\Context\ResolutionContext;
use Eloquent\Cosmos\Symbol\Symbol;
use PHPUnit_Framework_TestCase;

/**
 * @covers \Eloquent\Cosmos\Resolution\Context\Parser\ParsedResolutionContext
 * @covers \Eloquent\Cosmos\Resolution\Context\Parser\AbstractParsedElement
 */
class ParsedResolutionContextTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->context = new ResolutionContext;
        $this->symbols = array(Symbol::fromString('\SymbolA'), Symbol::fromString('\SymbolB'));
        $this->parsedContext = new ParsedResolutionContext($this->context, $this->symbols, 111, 222);
    }

    public function testConstructor()
    {
        $this->assertSame($this->context, $this->parsedContext->context());
        $this->assertSame($this->symbols, $this->parsedContext->symbols());
        $this->assertSame(111, $this->parsedContext->lineNumber());
        $this->assertSame(222, $this->parsedContext->columnNumber());
    }

    public function testConstructorDefaults()
    {
        $this->parsedContext = new ParsedResolutionContext;

        $this->assertEquals(new ResolutionContext, $this->parsedContext->context());
        $this->assertSame(array(), $this->parsedContext->symbols());
        $this->assertSame(0, $this->parsedContext->lineNumber());
        $this->assertSame(0, $this->parsedContext->columnNumber());
    }
}
