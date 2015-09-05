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

/**
 * @covers \Eloquent\Cosmos\Resolution\Context\ResolutionContext
 */
class ResolutionContextTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->primaryNamespace = Symbol::fromString('\VendorA\PackageA');
        $this->typeUseStatements = array(
            UseStatement::fromSymbol(Symbol::fromString('\NamespaceA\NamespaceB\SymbolA'), 'SymbolB'),
            UseStatement::fromSymbol(Symbol::fromString('\NamespaceC\NamespaceD')),
            UseStatement::fromSymbol(Symbol::fromString('\SymbolC')),
        );
        $this->functionUseStatements = array(
            UseStatement::fromSymbol(Symbol::fromString('\NamespaceE\NamespaceF\SymbolD'), 'SymbolE', 'function'),
            UseStatement::fromSymbol(Symbol::fromString('\NamespaceG\SymbolF'), null, 'function'),
            UseStatement::fromSymbol(Symbol::fromString('\SymbolC'), null, 'function'),
        );
        $this->constUseStatements = array(
            UseStatement::fromSymbol(Symbol::fromString('\NamespaceH\NamespaceI\SymbolG'), 'SymbolH', 'const'),
            UseStatement::fromSymbol(Symbol::fromString('\NamespaceJ\SymbolI'), null, 'const'),
            UseStatement::fromSymbol(Symbol::fromString('\SymbolC'), null, 'const'),
        );
        $this->useStatements =
            array_merge($this->typeUseStatements, $this->functionUseStatements, $this->constUseStatements);
        $this->subject = new ResolutionContext($this->primaryNamespace, $this->useStatements);
    }

    public function testCreate()
    {
        $this->assertEquals($this->subject, ResolutionContext::create($this->primaryNamespace, $this->useStatements));
    }

    public function testConstructor()
    {
        $this->assertSame($this->primaryNamespace, $this->subject->primaryNamespace());
        $this->assertSame($this->useStatements, $this->subject->useStatements());
    }

    public function testUseStatementsByType()
    {
        $this->assertSame($this->typeUseStatements, $this->subject->useStatementsByType(null));
        $this->assertSame($this->functionUseStatements, $this->subject->useStatementsByType('function'));
        $this->assertSame($this->constUseStatements, $this->subject->useStatementsByType('const'));
        $this->assertSame(array(), $this->subject->useStatementsByType('nonexistent'));
    }

    public function symbolByAtomData()
    {
        //                              type        atom          expected
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
     * @dataProvider symbolByAtomData
     */
    public function testSymbolByAtom($type, $atom, $expected)
    {
        if (null === $expected) {
            $this->assertNull($this->subject->symbolByAtom($atom, $type));
        } else {
            $this->assertSame($expected, strval($this->subject->symbolByAtom($atom, $type)));
        }
    }

    public function testToString()
    {
        $expected = <<<'EOD'
namespace VendorA\PackageA;

use NamespaceA\NamespaceB\SymbolA as SymbolB;
use NamespaceC\NamespaceD;
use SymbolC;
use function NamespaceE\NamespaceF\SymbolD as SymbolE;
use function NamespaceG\SymbolF;
use function SymbolC;
use const NamespaceH\NamespaceI\SymbolG as SymbolH;
use const NamespaceJ\SymbolI;
use const SymbolC;

EOD;

        $this->assertSame($expected, strval($this->subject));
    }

    public function testToStringWithNoUseStatements()
    {
        $this->subject = new ResolutionContext($this->primaryNamespace, array());
        $expected = <<<'EOD'
namespace VendorA\PackageA;

EOD;

        $this->assertSame($expected, strval($this->subject));
    }

    public function testToStringWithGlobalNamespace()
    {
        $this->subject = new ResolutionContext(null, $this->useStatements);
        $expected = <<<'EOD'
use NamespaceA\NamespaceB\SymbolA as SymbolB;
use NamespaceC\NamespaceD;
use SymbolC;
use function NamespaceE\NamespaceF\SymbolD as SymbolE;
use function NamespaceG\SymbolF;
use function SymbolC;
use const NamespaceH\NamespaceI\SymbolG as SymbolH;
use const NamespaceJ\SymbolI;
use const SymbolC;

EOD;

        $this->assertSame($expected, strval($this->subject));
    }

    public function testToStringWithGlobalNamespaceAndNoUseStatements()
    {
        $this->subject = new ResolutionContext(null, array());

        $this->assertSame('', strval($this->subject));
    }
}
