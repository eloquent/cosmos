<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Context;

use Eloquent\Cosmos\Symbol\Symbol;
use Eloquent\Cosmos\UseStatement\UseStatement;
use PHPUnit_Framework_TestCase;

class ResolutionContextTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->primaryNamespace = Symbol::fromString('\VendorA\PackageA');
        $this->useStatements = array(
            UseStatement::fromSymbol(Symbol::fromString('\VendorB\PackageB')),
            UseStatement::fromSymbol(Symbol::fromString('\VendorC\PackageC')),
            UseStatement::fromSymbol(Symbol::fromString('\VendorD\PackageD'), null, 'function'),
            UseStatement::fromSymbol(Symbol::fromString('\VendorE\PackageE'), null, 'function'),
            UseStatement::fromSymbol(Symbol::fromString('\VendorF\PackageF'), null, 'const'),
            UseStatement::fromSymbol(Symbol::fromString('\VendorG\PackageG'), null, 'const'),
        );
        $this->subject = new ResolutionContext($this->primaryNamespace, $this->useStatements);
    }

    public function testConstructor()
    {
        $this->assertSame($this->primaryNamespace, $this->subject->primaryNamespace());
        $this->assertSame($this->useStatements, $this->subject->useStatements());
    }

    public function testUseStatementsByType()
    {
        $typeUseStatements = array($this->useStatements[0], $this->useStatements[1]);
        $functionUseStatements = array($this->useStatements[2], $this->useStatements[3]);
        $constantUseStatements = array($this->useStatements[4], $this->useStatements[5]);

        $this->assertSame($typeUseStatements, $this->subject->useStatementsByType(null));
        $this->assertSame($functionUseStatements, $this->subject->useStatementsByType('function'));
        $this->assertSame($constantUseStatements, $this->subject->useStatementsByType('const'));
        $this->assertSame(array(), $this->subject->useStatementsByType('nonexistent'));
    }

    public function symbolByFirstAtomData()
    {
        //                              type        symbol        expected
        return array(
            'SymbolB'          => array(null,       'SymbolB',    '\NamespaceA\NamespaceB\SymbolA'),
            'NamespaceD'       => array(null,       'NamespaceD', '\NamespaceC\NamespaceD'),
            'SymbolC'          => array(null,       'SymbolC',    '\SymbolC'),
            'SymbolA'          => array(null,       'SymbolA',    null),
            'SymbolF'          => array(null,       'SymbolF',    null),

            'SymbolE function' => array('function', 'SymbolE',    '\NamespaceE\NamespaceF\SymbolD'),
            'SymbolF function' => array('function', 'SymbolF',    '\NamespaceG\SymbolF'),
            'SymbolC function' => array('function', 'SymbolC',    '\SymbolC'),
            'SymbolA function' => array('function', 'SymbolA',    null),
            'SymbolB function' => array('function', 'SymbolB',    null),

            'SymbolH const'    => array('const',    'SymbolH',    '\NamespaceH\NamespaceI\SymbolG'),
            'SymbolI const'    => array('const',    'SymbolI',    '\NamespaceJ\SymbolI'),
            'SymbolC const'    => array('const',    'SymbolC',    '\SymbolC'),
            'SymbolA const'    => array('const',    'SymbolA',    null),
            'SymbolB const'    => array('const',    'SymbolB',    null),
        );
    }

    /**
     * @dataProvider symbolByFirstAtomData
     */
    public function testSymbolByFirstAtom($type, $symbol, $expected)
    {
        $this->subject = new ResolutionContext(
            Symbol::fromString('\foo'),
            array(
                UseStatement::fromSymbol(Symbol::fromString('\NamespaceA\NamespaceB\SymbolA'), 'SymbolB'),
                UseStatement::fromSymbol(Symbol::fromString('\NamespaceC\NamespaceD')),
                UseStatement::fromSymbol(Symbol::fromString('\SymbolC')),

                UseStatement::fromSymbol(Symbol::fromString('\NamespaceE\NamespaceF\SymbolD'), 'SymbolE', 'function'),
                UseStatement::fromSymbol(Symbol::fromString('\NamespaceG\SymbolF'), null, 'function'),
                UseStatement::fromSymbol(Symbol::fromString('\SymbolC'), null, 'function'),

                UseStatement::fromSymbol(Symbol::fromString('\NamespaceH\NamespaceI\SymbolG'), 'SymbolH', 'const'),
                UseStatement::fromSymbol(Symbol::fromString('\NamespaceJ\SymbolI'), null, 'const'),
                UseStatement::fromSymbol(Symbol::fromString('\SymbolC'), null, 'const'),
            )
        );

        if (null === $expected) {
            $this->assertNull($this->subject->symbolByFirstAtom(Symbol::fromString($symbol), $type));
        } else {
            $this->assertSame($expected, strval($this->subject->symbolByFirstAtom(Symbol::fromString($symbol), $type)));
        }
    }
}
