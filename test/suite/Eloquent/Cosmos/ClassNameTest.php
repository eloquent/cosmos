<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2012 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Cosmos;

use Phake;
use PHPUnit_Framework_TestCase;

class ClassNameTest extends PHPUnit_Framework_TestCase
{
    public function testFromString()
    {
        $classNameA = ClassName::fromString('Foo');
        $classNameB = ClassName::fromString('\Foo');
        $classNameC = ClassName::fromString('Foo\Bar');
        $classNameD = ClassName::fromString('\Foo\Bar');

        $this->assertSame(array('Foo'), $classNameA->atoms());
        $this->assertSame(array('Foo'), $classNameB->atoms());
        $this->assertSame(array('Foo', 'Bar'), $classNameC->atoms());
        $this->assertSame(array('Foo', 'Bar'), $classNameD->atoms());
        $this->assertFalse($classNameA->isAbsolute());
        $this->assertTrue($classNameB->isAbsolute());
        $this->assertFalse($classNameC->isAbsolute());
        $this->assertTrue($classNameD->isAbsolute());
    }

    public function testFromStringFailureInvalidName()
    {
        $this->setExpectedException(
            __NAMESPACE__.'\Exception\InvalidClassNameAtomException',
            "Invalid class name atom ' foo '."
        );
        ClassName::fromString(' foo ');
    }

    public function testFromAtoms()
    {
        $classNameA = ClassName::fromAtoms(array('Foo'));
        $classNameB = ClassName::fromAtoms(array('Foo'), true);
        $classNameC = ClassName::fromAtoms(array('Foo', 'Bar'));
        $classNameD = ClassName::fromAtoms(array('Foo', 'Bar'), true);

        $this->assertSame(array('Foo'), $classNameA->atoms());
        $this->assertSame(array('Foo'), $classNameB->atoms());
        $this->assertSame(array('Foo', 'Bar'), $classNameC->atoms());
        $this->assertSame(array('Foo', 'Bar'), $classNameD->atoms());
        $this->assertFalse($classNameA->isAbsolute());
        $this->assertTrue($classNameB->isAbsolute());
        $this->assertFalse($classNameC->isAbsolute());
        $this->assertTrue($classNameD->isAbsolute());
    }

    public function testFromAtomsFailureInvalidAtom()
    {
        $this->setExpectedException(
            __NAMESPACE__.'\Exception\InvalidClassNameAtomException',
            "Invalid class name atom ' foo '."
        );
        ClassName::fromAtoms(array(' foo '));
    }

    public function testIsShortName()
    {
        $classNameA = ClassName::fromString('Foo');
        $classNameB = ClassName::fromString('\Foo');
        $classNameC = ClassName::fromString('Foo\Bar');
        $classNameD = ClassName::fromString('\Foo\Bar');

        $this->assertTrue($classNameA->isShortName());
        $this->assertFalse($classNameB->isShortName());
        $this->assertFalse($classNameC->isShortName());
        $this->assertFalse($classNameD->isShortName());
    }

    public function testJoin()
    {
        $classNameA = ClassName::fromString('Foo');
        $classNameB = ClassName::fromString('\Foo');
        $classNameC = ClassName::fromString('Bar\Baz');

        $this->assertEquals(
            ClassName::fromString('Foo\Bar\Baz'),
            $classNameA->join($classNameC)
        );
        $this->assertEquals(
            ClassName::fromString('\Foo\Bar\Baz'),
            $classNameB->join($classNameC)
        );
    }

    public function testJoinFailureAbsolute()
    {
        $classNameA = ClassName::fromString('Foo');
        $classNameB = ClassName::fromString('\Bar\Baz');

        $this->setExpectedException(
            __NAMESPACE__.'\Exception\AbsoluteJoinException'
        );
        $classNameA->join($classNameB);
    }

    public function testHasParent()
    {
        $classNameA = ClassName::fromString('Foo');
        $classNameB = ClassName::fromString('\Foo');
        $classNameC = ClassName::fromString('Foo\Bar');
        $classNameD = ClassName::fromString('\Foo\Bar');

        $this->assertFalse($classNameA->hasParent());
        $this->assertFalse($classNameB->hasParent());
        $this->assertTrue($classNameC->hasParent());
        $this->assertTrue($classNameD->hasParent());
    }

    public function testParent()
    {
        $this->assertEquals(
            ClassName::fromString('Foo\Bar'),
            ClassName::fromString('Foo\Bar\Baz')->parent()
        );
        $this->assertEquals(
            ClassName::fromString('\Foo\Bar'),
            ClassName::fromString('\Foo\Bar\Baz')->parent()
        );
    }

    public function testParentFailure()
    {
        $this->setExpectedException(
            __NAMESPACE__.'\Exception\ParentException'
        );
        ClassName::fromString('Foo')->parent();
    }

    public function testShortName()
    {
        $this->assertEquals(
            ClassName::fromString('Baz'),
            ClassName::fromString('Foo\Bar\Baz')->shortName()
        );
        $this->assertEquals(
            ClassName::fromString('Baz'),
            ClassName::fromString('\Foo\Bar\Baz')->shortName()
        );
    }

    public function testToAbsolute()
    {
        $this->assertEquals(
            ClassName::fromString('\Foo\Bar\Baz'),
            ClassName::fromString('Foo\Bar\Baz')->toAbsolute()
        );
        $this->assertEquals(
            ClassName::fromString('\Foo\Bar\Baz'),
            ClassName::fromString('\Foo\Bar\Baz')->toAbsolute()
        );
    }

    public function testHasDescendant()
    {
        $namespaceNameA = ClassName::fromString('Foo\Bar');
        $namespaceNameB = ClassName::fromString('\Foo\Bar');
        $classNameA = ClassName::fromString('Foo\Bar\Baz');
        $classNameB = ClassName::fromString('\Foo\Bar\Baz');
        $classNameC = ClassName::fromString('Bar\Baz');
        $classNameD = ClassName::fromString('\Bar\Baz');

        $this->assertTrue($namespaceNameA->hasDescendant($classNameA));
        $this->assertFalse($namespaceNameA->hasDescendant($classNameB));
        $this->assertFalse($namespaceNameA->hasDescendant($classNameC));
        $this->assertTrue($namespaceNameB->hasDescendant($classNameB));
        $this->assertFalse($namespaceNameB->hasDescendant($classNameA));
        $this->assertFalse($namespaceNameB->hasDescendant($classNameD));
    }

    public function testStripNamespace()
    {
        $namespaceNameA = ClassName::fromString('Foo\Bar');
        $namespaceNameB = ClassName::fromString('\Foo\Bar');
        $classNameA = ClassName::fromString('Foo\Bar\Baz');
        $classNameB = ClassName::fromString('Foo\Bar\Baz\Qux');
        $classNameC = ClassName::fromString('\Foo\Bar\Baz');
        $classNameD = ClassName::fromString('\Foo\Bar\Baz\Qux');

        $this->assertEquals(
            ClassName::fromString('Baz'),
            $classNameA->stripNamespace($namespaceNameA)
        );
        $this->assertEquals(
            ClassName::fromString('Baz\Qux'),
            $classNameB->stripNamespace($namespaceNameA)
        );
        $this->assertEquals(
            ClassName::fromString('Baz'),
            $classNameC->stripNamespace($namespaceNameB)
        );
        $this->assertEquals(
            ClassName::fromString('Baz\Qux'),
            $classNameD->stripNamespace($namespaceNameB)
        );
    }

    public function testStripNamespaceFailure()
    {
        $className = ClassName::fromString('Foo\Bar');
        $namespaceName = ClassName::fromString('Baz');

        $this->setExpectedException(
            __NAMESPACE__.'\Exception\NamespaceMismatchException'
        );
        $className->stripNamespace($namespaceName);
    }

    public function testExists()
    {
        $isolator = Phake::mock('Icecave\Isolator\Isolator');
        Phake::when($isolator)
            ->class_exists(Phake::anyParameters())
            ->thenReturn(true)
            ->thenReturn(false)
        ;
        $classNameA = ClassName::fromString('Foo');
        $classNameB = ClassName::fromString('\Bar');

        $this->assertTrue($classNameA->exists(null, $isolator));
        $this->assertFalse($classNameB->exists(false, $isolator));
        Phake::inOrder(
            Phake::verify($isolator)->class_exists('Foo', true),
            Phake::verify($isolator)->class_exists('\Bar', false)
        );
    }

    public function testString()
    {
        $classNameA = ClassName::fromString('Foo');
        $classNameB = ClassName::fromString('\Foo');
        $classNameC = ClassName::fromString('Foo\Bar');
        $classNameD = ClassName::fromString('\Foo\Bar');

        $this->assertSame('Foo', $classNameA->string());
        $this->assertSame('\Foo', $classNameB->string());
        $this->assertSame('Foo\Bar', $classNameC->string());
        $this->assertSame('\Foo\Bar', $classNameD->string());
        $this->assertSame('Foo', strval($classNameA));
        $this->assertSame('\Foo', strval($classNameB));
        $this->assertSame('Foo\Bar', strval($classNameC));
        $this->assertSame('\Foo\Bar', strval($classNameD));
    }
}
