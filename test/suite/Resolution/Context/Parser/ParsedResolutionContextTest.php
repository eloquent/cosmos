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
use Eloquent\Cosmos\UseStatement\UseStatement;
use Phake;
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

        $this->primaryNamespace = Symbol::fromString('\VendorA\PackageA');
        $this->useStatements = array(
            new UseStatement(Symbol::fromString('\VendorB\PackageB')),
            new UseStatement(Symbol::fromString('\VendorC\PackageC')),
        );
        $this->context = new ResolutionContext($this->primaryNamespace, $this->useStatements);
        $this->symbols = array(Symbol::fromString('\SymbolA'), Symbol::fromString('\SymbolB'));
        $this->position = new ParserPosition(111, 222);
        $this->parsedContext = new ParsedResolutionContext($this->context, $this->symbols, $this->position);
    }

    public function testConstructor()
    {
        $this->assertSame($this->context, $this->parsedContext->context());
        $this->assertSame($this->symbols, $this->parsedContext->symbols());
        $this->assertSame($this->position, $this->parsedContext->position());
        $this->assertSame($this->context->primaryNamespace(), $this->parsedContext->primaryNamespace());
        $this->assertSame($this->context->useStatements(), $this->parsedContext->useStatements());
    }

    public function testConstructorDefaults()
    {
        $this->parsedContext = new ParsedResolutionContext;

        $this->assertEquals(new ResolutionContext, $this->parsedContext->context());
        $this->assertSame(array(), $this->parsedContext->symbols());
        $this->assertEquals(new ParserPosition(0, 0), $this->parsedContext->position());
    }

    public function testSymbolByFirstAtom()
    {
        $this->context = new ResolutionContext(
            Symbol::fromString('\foo'),
            array(
                new UseStatement(Symbol::fromString('\My\Full\Classname'), Symbol::fromString('Another')),
                new UseStatement(Symbol::fromString('\My\Full\NSname')),
                new UseStatement(Symbol::fromString('\ArrayObject')),
            )
        );
        $this->parsedContext = new ParsedResolutionContext($this->context, $this->symbols, $this->position);

        $this->assertSame(
            '\My\Full\Classname',
            $this->parsedContext->symbolByFirstAtom(Symbol::fromString('Another'))->string()
        );
        $this->assertSame(
            '\My\Full\NSname',
            $this->parsedContext->symbolByFirstAtom(Symbol::fromString('NSname'))->string()
        );
        $this->assertSame(
            '\ArrayObject',
            $this->parsedContext->symbolByFirstAtom(Symbol::fromString('ArrayObject'))->string()
        );
        $this->assertNull($this->parsedContext->symbolByFirstAtom(Symbol::fromString('Classname')));
        $this->assertNull($this->parsedContext->symbolByFirstAtom(Symbol::fromString('FooClass')));
    }

    public function testAccept()
    {
        $visitor = Phake::mock('Eloquent\Cosmos\Resolution\Context\ResolutionContextVisitorInterface');
        $this->parsedContext->accept($visitor);

        Phake::verify($visitor)->visitResolutionContext($this->identicalTo($this->parsedContext));
    }
}
