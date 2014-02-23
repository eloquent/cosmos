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
}
