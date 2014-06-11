<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\ClassName\Factory;

use Eloquent\Cosmos\ClassName\ClassNameReference;
use Eloquent\Cosmos\ClassName\QualifiedClassName;
use Eloquent\Liberator\Liberator;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class ClassNameFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->factory = new ClassNameFactory;
    }

    public function createData()
    {
        //                                                 className              atoms                        isQualified
        return array(
            'Root namespace'                      => array('\\',                  array(),                     true),
            'Qualified'                           => array('\Namespace\Class',    array('Namespace', 'Class'), true),
            'Qualified with empty atoms'          => array('\Namespace\\\\Class', array('Namespace', 'Class'), true),
            'Qualified with empty atoms at start' => array('\\\\Class',           array('Class'),              true),
            'Qualified with empty atoms at end'   => array('\Class\\\\',          array('Class'),              true),

            'Empty'                               => array('',                    array('.'),                  false),
            'Self'                                => array('.',                   array('.'),                  false),
            'Reference'                           => array('Namespace\Class',     array('Namespace', 'Class'), false),
            'Reference with trailing separator'   => array('Namespace\Class\\',   array('Namespace', 'Class'), false),
            'Reference with empty atoms'          => array('Namespace\\\\Class',  array('Namespace', 'Class'), false),
            'Reference with empty atoms at end'   => array('Namespace\Class\\\\', array('Namespace', 'Class'), false),
        );
    }

    /**
     * @dataProvider createData
     */
    public function testCreate($classNameString, array $atoms, $isQualified)
    {
        $className = $this->factory->create($classNameString);

        $this->assertSame($atoms, $className->atoms());
        $this->assertSame($isQualified, $className instanceof QualifiedClassName);
        $this->assertSame($isQualified, !$className instanceof ClassNameReference);
    }

    /**
     * @dataProvider createData
     */
    public function testCreateFromAtoms($pathString, array $atoms, $isQualified)
    {
        $className = $this->factory->createFromAtoms($atoms, $isQualified);

        $this->assertSame($atoms, $className->atoms());
        $this->assertSame($isQualified, $className instanceof QualifiedClassName);
        $this->assertSame($isQualified, !$className instanceof ClassNameReference);
    }

    public function testCreateFromAtomsDefaults()
    {
        $className = $this->factory->createFromAtoms(array());

        $this->assertTrue($className instanceof QualifiedClassName);
    }

    public function createRuntimeData()
    {
        //                                                 className              atoms
        return array(
            'Root namespace'                      => array('\\',                  array()),
            'Qualified'                           => array('\Namespace\Class',    array('Namespace', 'Class')),
            'Qualified with empty atoms'          => array('\Namespace\\\\Class', array('Namespace', 'Class')),
            'Qualified with empty atoms at start' => array('\\\\Class',           array('Class')),
            'Qualified with empty atoms at end'   => array('\Class\\\\',          array('Class')),

            'Empty'                               => array('',                    array()),
            'Self'                                => array('.',                   array('.')),
            'Reference'                           => array('Namespace\Class',     array('Namespace', 'Class')),
            'Reference with trailing separator'   => array('Namespace\Class\\',   array('Namespace', 'Class')),
            'Reference with empty atoms'          => array('Namespace\\\\Class',  array('Namespace', 'Class')),
            'Reference with empty atoms at end'   => array('Namespace\Class\\\\', array('Namespace', 'Class')),
        );
    }

    /**
     * @dataProvider createRuntimeData
     */
    public function testCreateRuntime($classNameString, array $atoms)
    {
        $className = $this->factory->createRuntime($classNameString);

        $this->assertSame($atoms, $className->atoms());
        $this->assertTrue($className instanceof QualifiedClassName);
    }

    public function testCreateFromObject()
    {
        $this->assertSame(
            '\Eloquent\Cosmos\ClassName\Factory\ClassNameFactory',
            $this->factory->createFromObject($this->factory)->string()
        );
    }

    public function testCreateFromReflector()
    {
        $reflector = new ReflectionClass('Eloquent\Cosmos\ClassName\Factory\ClassNameFactory');

        $this->assertSame(
            '\Eloquent\Cosmos\ClassName\Factory\ClassNameFactory',
            $this->factory->createFromReflector($reflector)->string()
        );
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
