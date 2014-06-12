<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Symbol;

use PHPUnit_Framework_TestCase;
use ReflectionClass;

class SymbolTest extends PHPUnit_Framework_TestCase
{
    public function createData()
    {
        //                                                 symbol                  atoms                         isQualified
        return array(
            'Root namespace'                      => array('\\',                   array(),                      true),
            'Qualified'                           => array('\Namespace\Symbol',    array('Namespace', 'Symbol'), true),
            'Qualified with empty atoms'          => array('\Namespace\\\\Symbol', array('Namespace', 'Symbol'), true),
            'Qualified with empty atoms at start' => array('\\\\Symbol',           array('Symbol'),              true),
            'Qualified with empty atoms at end'   => array('\Symbol\\\\',          array('Symbol'),              true),

            'Empty'                               => array('',                     array('.'),                   false),
            'Self'                                => array('.',                    array('.'),                   false),
            'Reference'                           => array('Namespace\Symbol',     array('Namespace', 'Symbol'), false),
            'Reference with trailing separator'   => array('Namespace\Symbol\\',   array('Namespace', 'Symbol'), false),
            'Reference with empty atoms'          => array('Namespace\\\\Symbol',  array('Namespace', 'Symbol'), false),
            'Reference with empty atoms at end'   => array('Namespace\Symbol\\\\', array('Namespace', 'Symbol'), false),
        );
    }

    /**
     * @dataProvider createData
     */
    public function testFromString($symbolString, array $atoms, $isQualified)
    {
        $symbol = Symbol::fromString($symbolString);

        $this->assertSame($atoms, $symbol->atoms());
        $this->assertSame($isQualified, $symbol instanceof QualifiedSymbolInterface);
        $this->assertSame($isQualified, !$symbol instanceof SymbolReferenceInterface);
    }

    /**
     * @dataProvider createData
     */
    public function testFromAtoms($pathString, array $atoms, $isQualified)
    {
        $symbol = Symbol::fromAtoms($atoms, $isQualified);

        $this->assertSame($atoms, $symbol->atoms());
        $this->assertSame($isQualified, $symbol instanceof QualifiedSymbolInterface);
        $this->assertSame($isQualified, !$symbol instanceof SymbolReferenceInterface);
    }

    public function testFromAtomsDefaults()
    {
        $symbol = Symbol::fromAtoms(array());

        $this->assertTrue($symbol instanceof QualifiedSymbolInterface);
    }

    public function fromRuntimeStringData()
    {
        //                                                 symbol                  atoms
        return array(
            'Root namespace'                      => array('\\',                   array()),
            'Qualified'                           => array('\Namespace\Symbol',    array('Namespace', 'Symbol')),
            'Qualified with empty atoms'          => array('\Namespace\\\\Symbol', array('Namespace', 'Symbol')),
            'Qualified with empty atoms at start' => array('\\\\Symbol',           array('Symbol')),
            'Qualified with empty atoms at end'   => array('\Symbol\\\\',          array('Symbol')),

            'Empty'                               => array('',                     array()),
            'Self'                                => array('.',                    array('.')),
            'Reference'                           => array('Namespace\Symbol',     array('Namespace', 'Symbol')),
            'Reference with trailing separator'   => array('Namespace\Symbol\\',   array('Namespace', 'Symbol')),
            'Reference with empty atoms'          => array('Namespace\\\\Symbol',  array('Namespace', 'Symbol')),
            'Reference with empty atoms at end'   => array('Namespace\Symbol\\\\', array('Namespace', 'Symbol')),
        );
    }

    /**
     * @dataProvider fromRuntimeStringData
     */
    public function testFromRuntimeString($symbolString, array $atoms)
    {
        $symbol = Symbol::fromRuntimeString($symbolString);

        $this->assertSame($atoms, $symbol->atoms());
        $this->assertTrue($symbol instanceof QualifiedSymbol);
    }

    public function testFromObject()
    {
        $symbol = Symbol::fromString('\Symbol');

        $this->assertSame('\Eloquent\Cosmos\Symbol\QualifiedSymbol', Symbol::fromObject($symbol)->string());
    }

    public function testFromClass()
    {
        $class = new ReflectionClass('Eloquent\Cosmos\Symbol\Factory\SymbolFactory');

        $this->assertSame('\Eloquent\Cosmos\Symbol\Factory\SymbolFactory', Symbol::fromClass($class)->string());
    }

    public function testGlobalNamespace()
    {
        $symbol = Symbol::globalNamespace();

        $this->assertSame(array(), $symbol->atoms());
        $this->assertTrue($symbol instanceof QualifiedSymbol);
        $this->assertSame($symbol, Symbol::globalNamespace());
    }
}
