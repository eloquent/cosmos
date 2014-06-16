<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Symbol\Factory;

use Eloquent\Cosmos\Symbol\QualifiedSymbol;
use Eloquent\Cosmos\Symbol\SymbolReference;
use Eloquent\Liberator\Liberator;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionFunction;

class SymbolFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->factory = new SymbolFactory;
    }

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
    public function testCreate($symbolString, array $atoms, $isQualified)
    {
        $symbol = $this->factory->create($symbolString);

        $this->assertSame($atoms, $symbol->atoms());
        $this->assertSame($isQualified, $symbol instanceof QualifiedSymbol);
        $this->assertSame($isQualified, !$symbol instanceof SymbolReference);
    }

    /**
     * @dataProvider createData
     */
    public function testCreateFromAtoms($pathString, array $atoms, $isQualified)
    {
        $symbol = $this->factory->createFromAtoms($atoms, $isQualified);

        $this->assertSame($atoms, $symbol->atoms());
        $this->assertSame($isQualified, $symbol instanceof QualifiedSymbol);
        $this->assertSame($isQualified, !$symbol instanceof SymbolReference);
    }

    public function testCreateFromAtomsDefaults()
    {
        $symbol = $this->factory->createFromAtoms(array());

        $this->assertTrue($symbol instanceof QualifiedSymbol);
    }

    public function createRuntimeData()
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
     * @dataProvider createRuntimeData
     */
    public function testCreateRuntime($symbolString, array $atoms)
    {
        $symbol = $this->factory->createRuntime($symbolString);

        $this->assertSame($atoms, $symbol->atoms());
        $this->assertTrue($symbol instanceof QualifiedSymbol);
    }

    public function testCreateFromObject()
    {
        $this->assertSame(
            '\Eloquent\Cosmos\Symbol\Factory\SymbolFactory',
            $this->factory->createFromObject($this->factory)->string()
        );
    }

    public function testCreateFromClass()
    {
        $class = new ReflectionClass('Eloquent\Cosmos\Symbol\Factory\SymbolFactory');

        $this->assertSame(
            '\Eloquent\Cosmos\Symbol\Factory\SymbolFactory',
            $this->factory->createFromClass($class)->string()
        );
    }

    public function testCreateFromFunction()
    {
        $function = new ReflectionFunction('printf');

        $this->assertSame('\printf', $this->factory->createFromFunction($function)->string());
    }

    public function testGlobalNamespace()
    {
        $symbol = $this->factory->globalNamespace();

        $this->assertSame(array(), $symbol->atoms());
        $this->assertTrue($symbol instanceof QualifiedSymbol);
        $this->assertSame($symbol, $this->factory->globalNamespace());
    }

    public function testInstance()
    {
        $class = get_class($this->factory);
        $liberatedClass = Liberator::liberateClass($class);
        $liberatedClass->instance = null;
        $actual = $class::instance();

        $this->assertInstanceOf($class, $actual);
        $this->assertSame($actual, $class::instance());
    }
}
