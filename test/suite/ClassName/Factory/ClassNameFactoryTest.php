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

use Eloquent\Cosmos\ClassName\ClassNameReferenceInterface;
use Eloquent\Cosmos\ClassName\QualifiedClassNameInterface;
use Eloquent\Liberator\Liberator;
use PHPUnit_Framework_TestCase;

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
        $this->assertSame($isQualified, $className instanceof QualifiedClassNameInterface);
        $this->assertSame($isQualified, !$className instanceof ClassNameReferenceInterface);
    }

    /**
     * @dataProvider createData
     */
    public function testCreateFromAtoms($pathString, array $atoms, $isQualified)
    {
        $className = $this->factory->createFromAtoms($atoms, $isQualified);

        $this->assertSame($atoms, $className->atoms());
        $this->assertSame($isQualified, $className instanceof QualifiedClassNameInterface);
        $this->assertSame($isQualified, !$className instanceof ClassNameReferenceInterface);
    }

    public function testCreateFromAtomsDefaults()
    {
        $className = $this->factory->createFromAtoms(array());

        $this->assertTrue($className instanceof QualifiedClassNameInterface);
    }

    public function testInstance()
    {
        $class = Liberator::liberateClass(__NAMESPACE__ . '\ClassNameFactory');
        $class->instance = null;
        $actual = ClassNameFactory::instance();

        $this->assertInstanceOf(__NAMESPACE__ . '\ClassNameFactory', $actual);
        $this->assertSame($actual, ClassNameFactory::instance());
    }
}
