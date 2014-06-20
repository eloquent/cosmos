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
use Eloquent\Cosmos\Resolution\Context\ResolutionContext;
use Eloquent\Cosmos\Symbol\Symbol;
use Eloquent\Cosmos\Symbol\SymbolType;
use Eloquent\Cosmos\UseStatement\UseStatement;
use Eloquent\Cosmos\UseStatement\UseStatementType;
use PHPUnit_Framework_TestCase;
use Phake;

/**
 * @covers \Eloquent\Cosmos\Resolution\Context\Parser\Element\ParsedResolutionContext
 * @covers \Eloquent\Cosmos\Resolution\Context\Parser\Element\AbstractParsedElement
 */
class ParsedResolutionContextTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->primaryNamespace = Symbol::fromString('\VendorA\PackageA');
        $this->useStatements = array(
            UseStatement::create(Symbol::fromString('\VendorB\PackageB')),
            UseStatement::create(Symbol::fromString('\VendorC\PackageC')),
            UseStatement::create(Symbol::fromString('\VendorD\PackageD'), null, UseStatementType::FUNCT1ON()),
            UseStatement::create(Symbol::fromString('\VendorE\PackageE'), null, UseStatementType::FUNCT1ON()),
            UseStatement::create(Symbol::fromString('\VendorF\PackageF'), null, UseStatementType::CONSTANT()),
            UseStatement::create(Symbol::fromString('\VendorG\PackageG'), null, UseStatementType::CONSTANT()),
        );
        $this->innerContext = new ResolutionContext($this->primaryNamespace, $this->useStatements);
        $this->symbols = array(Symbol::fromString('\SymbolA'), Symbol::fromString('\SymbolB'));
        $this->position = new ParserPosition(111, 222);
        $this->context = new ParsedResolutionContext(
            $this->innerContext,
            $this->symbols,
            $this->position,
            333,
            444,
            555,
            666
        );
    }

    public function testConstructor()
    {
        $this->assertSame($this->innerContext, $this->context->context());
        $this->assertSame($this->symbols, $this->context->symbols());
        $this->assertSame($this->position, $this->context->position());
        $this->assertSame(333, $this->context->offset());
        $this->assertSame(444, $this->context->size());
        $this->assertSame(555, $this->context->namespaceSymbolOffset());
        $this->assertSame(666, $this->context->namespaceSymbolSize());
        $this->assertSame($this->innerContext->primaryNamespace(), $this->context->primaryNamespace());
        $this->assertSame($this->innerContext->useStatements(), $this->context->useStatements());
    }

    public function testConstructorDefaults()
    {
        $this->context = new ParsedResolutionContext;

        $this->assertEquals(new ResolutionContext, $this->context->context());
        $this->assertSame(array(), $this->context->symbols());
        $this->assertEquals(new ParserPosition(0, 0), $this->context->position());
        $this->assertSame(0, $this->context->offset());
        $this->assertSame(0, $this->context->size());
        $this->assertNull($this->context->namespaceSymbolOffset());
        $this->assertNull($this->context->namespaceSymbolSize());
    }

    public function testUseStatementsByType()
    {
        $typeUseStatements = array($this->useStatements[0], $this->useStatements[1]);
        $functionUseStatements = array($this->useStatements[2], $this->useStatements[3]);
        $constantUseStatements = array($this->useStatements[4], $this->useStatements[5]);

        $this->assertSame($typeUseStatements, $this->context->useStatementsByType(UseStatementType::TYPE()));
        $this->assertSame($functionUseStatements, $this->context->useStatementsByType(UseStatementType::FUNCT1ON()));
        $this->assertSame($constantUseStatements, $this->context->useStatementsByType(UseStatementType::CONSTANT()));
    }

    public function testSymbolByFirstAtom()
    {
        $this->innerContext = new ResolutionContext(
            Symbol::fromString('\foo'),
            array(
                UseStatement::create(
                    Symbol::fromString('\NamespaceA\NamespaceB\SymbolA'),
                    Symbol::fromString('SymbolB')
                ),
                UseStatement::create(Symbol::fromString('\NamespaceC\NamespaceD')),
                UseStatement::create(Symbol::fromString('\SymbolC')),

                UseStatement::create(Symbol::fromString(
                    '\NamespaceE\NamespaceF\SymbolD'),
                    Symbol::fromString('SymbolE'),
                    UseStatementType::FUNCT1ON()
                ),
                UseStatement::create(Symbol::fromString('\NamespaceG\SymbolF'), null, UseStatementType::FUNCT1ON()),
                UseStatement::create(Symbol::fromString('\SymbolC'), null, UseStatementType::FUNCT1ON()),

                UseStatement::create(Symbol::fromString(
                    '\NamespaceH\NamespaceI\SymbolG'),
                    Symbol::fromString('SymbolH'),
                    UseStatementType::CONSTANT()
                ),
                UseStatement::create(Symbol::fromString('\NamespaceJ\SymbolI'), null, UseStatementType::CONSTANT()),
                UseStatement::create(Symbol::fromString('\SymbolC'), null, UseStatementType::CONSTANT()),
            )
        );
        $this->context = new ParsedResolutionContext($this->innerContext, $this->symbols, $this->position);

        $this->assertSame(
            '\NamespaceA\NamespaceB\SymbolA',
            $this->context->symbolByFirstAtom(Symbol::fromString('SymbolB'))->string()
        );
        $this->assertSame(
            '\NamespaceC\NamespaceD',
            $this->context->symbolByFirstAtom(Symbol::fromString('NamespaceD'))->string()
        );
        $this->assertSame('\SymbolC', $this->context->symbolByFirstAtom(Symbol::fromString('SymbolC'))->string());
        $this->assertNull($this->context->symbolByFirstAtom(Symbol::fromString('SymbolA')));
        $this->assertNull($this->context->symbolByFirstAtom(Symbol::fromString('SymbolF')));

        $this->assertSame(
            '\NamespaceE\NamespaceF\SymbolD',
            $this->context->symbolByFirstAtom(Symbol::fromString('SymbolE'), SymbolType::FUNCT1ON())->string()
        );
        $this->assertSame(
            '\NamespaceG\SymbolF',
            $this->context->symbolByFirstAtom(Symbol::fromString('SymbolF'), SymbolType::FUNCT1ON())->string()
        );
        $this->assertSame(
            '\SymbolC',
            $this->context->symbolByFirstAtom(Symbol::fromString('SymbolC'), SymbolType::FUNCT1ON())->string()
        );
        $this->assertNull($this->context->symbolByFirstAtom(Symbol::fromString('SymbolA'), SymbolType::FUNCT1ON()));
        $this->assertNull($this->context->symbolByFirstAtom(Symbol::fromString('SymbolB'), SymbolType::FUNCT1ON()));

        $this->assertSame(
            '\NamespaceH\NamespaceI\SymbolG',
            $this->context->symbolByFirstAtom(Symbol::fromString('SymbolH'), SymbolType::CONSTANT())->string()
        );
        $this->assertSame(
            '\NamespaceJ\SymbolI',
            $this->context->symbolByFirstAtom(Symbol::fromString('SymbolI'), SymbolType::CONSTANT())->string()
        );
        $this->assertSame(
            '\SymbolC',
            $this->context->symbolByFirstAtom(Symbol::fromString('SymbolC'), SymbolType::CONSTANT())->string()
        );
        $this->assertNull($this->context->symbolByFirstAtom(Symbol::fromString('SymbolA'), SymbolType::CONSTANT()));
        $this->assertNull($this->context->symbolByFirstAtom(Symbol::fromString('SymbolB'), SymbolType::CONSTANT()));
    }

    public function testAccept()
    {
        $visitor = Phake::mock('Eloquent\Cosmos\Resolution\Context\ResolutionContextVisitorInterface');
        $this->context->accept($visitor);

        Phake::verify($visitor)->visitResolutionContext($this->identicalTo($this->context));
    }
}
