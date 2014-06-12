<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\ClassName;

use PHPUnit_Framework_TestCase;
use ReflectionClass;

class ClassNameTest extends PHPUnit_Framework_TestCase
{
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
    public function testFromString($classNameString, array $atoms, $isQualified)
    {
        $className = ClassName::fromString($classNameString);

        $this->assertSame($atoms, $className->atoms());
        $this->assertSame($isQualified, $className instanceof QualifiedClassNameInterface);
        $this->assertSame($isQualified, !$className instanceof ClassNameReferenceInterface);
    }

    /**
     * @dataProvider createData
     */
    public function testFromAtoms($pathString, array $atoms, $isQualified)
    {
        $className = ClassName::fromAtoms($atoms, $isQualified);

        $this->assertSame($atoms, $className->atoms());
        $this->assertSame($isQualified, $className instanceof QualifiedClassNameInterface);
        $this->assertSame($isQualified, !$className instanceof ClassNameReferenceInterface);
    }

    public function testFromAtomsDefaults()
    {
        $className = ClassName::fromAtoms(array());

        $this->assertTrue($className instanceof QualifiedClassNameInterface);
    }

    public function fromRuntimeStringData()
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
     * @dataProvider fromRuntimeStringData
     */
    public function testFromRuntimeString($classNameString, array $atoms)
    {
        $className = ClassName::fromRuntimeString($classNameString);

        $this->assertSame($atoms, $className->atoms());
        $this->assertTrue($className instanceof QualifiedClassName);
    }

    public function testFromObject()
    {
        $className = ClassName::fromString('\Class');

        $this->assertSame('\Eloquent\Cosmos\ClassName\QualifiedClassName', ClassName::fromObject($className)->string());
    }

    public function testFromReflector()
    {
        $reflector = new ReflectionClass('Eloquent\Cosmos\ClassName\Factory\ClassNameFactory');

        $this->assertSame(
            '\Eloquent\Cosmos\ClassName\Factory\ClassNameFactory',
            ClassName::fromReflector($reflector)->string()
        );
    }

    public function testGlobalNamespace()
    {
        $className = ClassName::globalNamespace();

        $this->assertSame(array(), $className->atoms());
        $this->assertTrue($className instanceof QualifiedClassName);
        $this->assertSame($className, ClassName::globalNamespace());
    }
}
