<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Symbol;

use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionFunction;

/**
 * @covers \Eloquent\Cosmos\Symbol\Symbol
 */
class SymbolTest extends PHPUnit_Framework_TestCase
{
    public function symbolData()
    {
        //                       symbol               atoms                         isQualified
        return array(
            'Qualified' => array('\Namespace\Symbol', array('Namespace', 'Symbol'), true),
            'Reference' => array('Namespace\Symbol',  array('Namespace', 'Symbol'), false),
        );
    }

    /**
     * @dataProvider symbolData
     */
    public function testFromString($string, array $atoms, $isQualified)
    {
        $symbol = Symbol::fromString($string);

        $this->assertSame($atoms, $symbol->atoms());
        $this->assertSame($isQualified, $symbol->isQualified());
    }

    /**
     * @dataProvider symbolData
     */
    public function testFromRuntimeString($string, array $atoms)
    {
        $symbol = Symbol::fromRuntimeString($string);

        $this->assertSame($atoms, $symbol->atoms());
        $this->assertTrue($symbol->isQualified());
    }

    /**
     * @dataProvider symbolData
     */
    public function testFromAtoms($string, array $atoms, $isQualified)
    {
        $symbol = Symbol::fromAtoms($atoms, $isQualified);

        $this->assertSame($atoms, $symbol->atoms());
        $this->assertSame($isQualified, $symbol->isQualified());
    }

    public function testFromAtomsDefaults()
    {
        $symbol = Symbol::fromAtoms(array('Atom'));

        $this->assertTrue($symbol->isQualified());
    }

    public function testFromObject()
    {
        $this->assertSame('\Eloquent\Cosmos\Symbol\SymbolTest', strval(Symbol::fromObject($this)));
    }

    public function testFromClass()
    {
        $class = new ReflectionClass('Eloquent\Cosmos\Symbol\Symbol');

        $this->assertSame('\Eloquent\Cosmos\Symbol\Symbol', strval(Symbol::fromClass($class)));
    }

    public function testFromFunction()
    {
        $function = new ReflectionFunction('printf');

        $this->assertSame('\printf', strval(Symbol::fromFunction($function)));
    }

    public function testConstructorFailureEmpty()
    {
        $this->setExpectedException('InvalidArgumentException', 'Symbols cannot be empty.');
        new Symbol(array(), true);
    }

    /**
     * @dataProvider symbolData
     */
    public function testToString($string, array $atoms, $isQualified)
    {
        $this->assertSame($string, strval(Symbol::fromAtoms($atoms, $isQualified)));
    }

    public function testRuntimeString()
    {
        $this->assertSame('Namespace\Symbol', Symbol::fromString('Namespace\Symbol')->runtimeString());
        $this->assertSame('Namespace\Symbol', Symbol::fromString('\Namespace\Symbol')->runtimeString());
    }
}
