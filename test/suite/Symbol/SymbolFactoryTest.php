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

use Eloquent\Cosmos\Symbol\QualifiedSymbol;
use Eloquent\Cosmos\Symbol\SymbolReference;
use Eloquent\Liberator\Liberator;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionFunction;

/**
 * @covers \Eloquent\Cosmos\Symbol\SymbolFactory
 */
class SymbolFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->subject = new SymbolFactory();
    }

    public function symbolData()
    {
        //                       symbol               atoms                         isQualified
        return array(
            'Qualified' => array('\Namespace\Symbol', array('Namespace', 'Symbol'), true),
            'Reference' => array('Namespace\Symbol',  array('Namespace', 'Symbol'), false),
        );
    }

    public function invalidAtomData()
    {
        //                               symbol atoms
        return array(
            'Empty'             => array('',    array('')),
            'Invalid character' => array('$',   array('$')),
            'Backslash'         => array('\\',  array('', '')),
        );
    }

    /**
     * @dataProvider symbolData
     */
    public function testCreateFromString($string, array $atoms, $isQualified)
    {
        $symbol = $this->subject->createFromString($string);

        $this->assertSame($atoms, $symbol->atoms());
        $this->assertSame($isQualified, $symbol->isQualified());
    }

    /**
     * @dataProvider invalidAtomData
     */
    public function testCreateFromStringFailureInvalidAtom($string, $atoms)
    {
        $this->setExpectedException('Eloquent\Cosmos\Exception\InvalidSymbolAtomException');
        $this->subject->createFromString($string);
    }

    /**
     * @dataProvider symbolData
     */
    public function testCreateFromRuntimeString($string, array $atoms)
    {
        $symbol = $this->subject->createFromRuntimeString($string);

        $this->assertSame($atoms, $symbol->atoms());
        $this->assertTrue($symbol->isQualified());
    }

    /**
     * @dataProvider invalidAtomData
     */
    public function testCreateFromRuntimeStringFailureInvalidAtom($string, $atoms)
    {
        $this->setExpectedException('Eloquent\Cosmos\Exception\InvalidSymbolAtomException');
        $this->subject->createFromRuntimeString($string);
    }

    /**
     * @dataProvider symbolData
     */
    public function testCreateFromAtoms($string, array $atoms, $isQualified)
    {
        $symbol = $this->subject->createFromAtoms($atoms, $isQualified);

        $this->assertSame($atoms, $symbol->atoms());
        $this->assertSame($isQualified, $symbol->isQualified());
    }

    /**
     * @dataProvider invalidAtomData
     */
    public function testCreateFromAtomsFailureInvalidAtom($string, $atoms)
    {
        $this->setExpectedException('Eloquent\Cosmos\Exception\InvalidSymbolAtomException');
        $this->subject->createFromAtoms($atoms);
    }

    public function testCreateFromAtomsDefaults()
    {
        $symbol = $this->subject->createFromAtoms(array());

        $this->assertTrue($symbol->isQualified());
    }

    public function testInstance()
    {
        $class = get_class($this->subject);
        $liberatedClass = Liberator::liberateClass($class);
        $liberatedClass->instance = null;
        $actual = $class::instance();

        $this->assertInstanceOf($class, $actual);
        $this->assertSame($actual, $class::instance());
    }
}
