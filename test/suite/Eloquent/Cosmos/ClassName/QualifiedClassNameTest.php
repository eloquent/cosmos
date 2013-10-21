<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Cosmos\ClassName;

use Phake;
use PHPUnit_Framework_TestCase;

class QualifiedClassNameTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->factory = new Factory\ClassNameFactory;
    }

    public function classNameData()
    {
        //                             className                 atoms
        return array(
            'Root namespace'  => array('\\',                     array()),
            'Single atom'     => array('\\Class',                array('Class')),
            'Multiple atoms'  => array('\\Namespace\\Class',     array('Namespace', 'Class')),
            'Parent atom'     => array('\\Namespace\\..\\Class', array('Namespace', '..', 'Class')),
            'Self atom'       => array('\\Namespace\\.\\Class',  array('Namespace', '.', 'Class')),
        );
    }

    /**
     * @dataProvider classNameData
     */
    public function testConstructor($classNameString, array $atoms)
    {
        $className = $this->factory->create($classNameString);

        $this->assertSame($atoms, $className->atoms());
        $this->assertSame($classNameString, $className->string());
        $this->assertSame($classNameString, strval($className));
    }

    public function testConstructorFailureInvalidAtom()
    {
        $this->setExpectedException(__NAMESPACE__ . '\Exception\InvalidClassNameAtomException');

        $this->factory->create('\\Namespace\\Class-Name');
    }

    public function namePartData()
    {
        //                                             className         name            nameWithoutExtension  namePrefix  nameSuffix  extension
        return array(
            'Root namespace'                  => array('\\',             '',             '',                   '',         null,       null),
            'No extensions'                   => array('\\foo',          'foo',          'foo',                'foo',      null,       null),
            'Empty extension'                 => array('\\foo_',         'foo_',         'foo',                'foo',      '',         ''),
            'Single extension'                => array('\\foo_bar',      'foo_bar',      'foo',                'foo',      'bar',      'bar'),
            'Multiple extensions'             => array('\\foo_bar_baz',  'foo_bar_baz',  'foo_bar',            'foo',      'bar_baz',  'baz'),
            'No name with single extension'   => array('\\_foo',         '_foo',         '',                   '',         'foo',      'foo'),
            'No name with multiple extension' => array('\\_foo_bar',     '_foo_bar',     '_foo',               '',         'foo_bar',  'bar'),
        );
    }

    /**
     * @dataProvider namePartData
     */
    public function testNamePartMethods($classNameString, $name, $nameWithoutExtension, $namePrefix, $nameSuffix, $extension)
    {
        $className = $this->factory->create($classNameString);

        $this->assertSame($name, $className->name());
        $this->assertSame($nameWithoutExtension, $className->nameWithoutExtension());
        $this->assertSame($namePrefix, $className->namePrefix());
        $this->assertSame($nameSuffix, $className->nameSuffix());
        $this->assertSame($extension, $className->extension());
        $this->assertSame(null !== $extension, $className->hasExtension());
    }

    public function joinData()
    {
        //                                              className     reference   expectedResult
        return array(
            'Root namespace'                   => array('\\',         'foo',      '\\foo'),
            'Single atom'                      => array('\\foo',      'bar',      '\\foo\\bar'),
            'Multiple atoms'                   => array('\\foo',      'bar\\baz', '\\foo\\bar\\baz'),
            'Multiple atoms to multiple atoms' => array('\\foo\\bar', 'baz\\qux', '\\foo\\bar\\baz\\qux'),
            'Special atoms'                    => array('\\foo',      '.\\..',    '\\foo\\.\\..'),
        );
    }

    /**
     * @dataProvider joinData
     */
    public function testJoin($classNameString, $referenceString, $expectedResultString)
    {
        $className = $this->factory->create($classNameString);
        $reference = $this->factory->create($referenceString);
        $result = $className->join($reference);

        $this->assertSame($expectedResultString, $result->string());
    }

    public function testJoinFailureQualified()
    {
        $className = $this->factory->create('\\foo');
        $reference = $this->factory->create('\\bar');

        $this->setExpectedException('PHPUnit_Framework_Error');
        $className->join($reference);
    }

    public function relativeToData()
    {
        //                                        parent                   child                    expectedResult
        return array(
            'Self'                       => array('\\foo',                 '\\foo',                 '.'),
            'Child'                      => array('\\foo',                 '\\foo\\bar',            'bar'),
            'Ancestor'                   => array('\\foo',                 '\\foo\\bar\\baz',       'bar\\baz'),
            'Sibling'                    => array('\\foo',                 '\\bar',                 '..\\bar'),
            'Parent\'s sibling'          => array('\\foo\\bar\\baz',       '\\foo\\qux',            '..\\..\\qux'),
            'Parent\'s sibling\'s child' => array('\\foo\\bar\\baz',       '\\foo\\qux\\doom',      '..\\..\\qux\\doom'),
            'Completely unrelated'       => array('\\foo\\bar\\baz',       '\\qux\\doom',           '..\\..\\..\\qux\\doom'),
            'Lengthly unrelated child'   => array('\\foo\\bar',            '\\baz\\qux\\doom',      '..\\..\\baz\\qux\\doom'),
            'Common suffix'              => array('\\foo\\bar\\baz\\doom', '\\foo\\bar\\qux\\doom', '..\\..\\qux\\doom'),
        );
    }

    /**
     * @dataProvider relativeToData
     */
    public function testRelativeTo($parentString, $childString, $expectedResultString)
    {
        $parent = $this->factory->create($parentString);
        $child = $this->factory->create($childString);
        $result = $child->relativeTo($parent);

        $this->assertSame($expectedResultString, $result->string());
    }

    public function testNormalize()
    {
        $className = $this->factory->create('\\foo\\..\\bar');
        $normalizedClassName = $this->factory->create('\\bar');

        $this->assertEquals($normalizedClassName, $className->normalize());
    }

    public function testNormalizeCustomNormalizer()
    {
        $className = $this->factory->create('\\foo\\..\\bar');
        $normalizedClassName = $this->factory->create('\\bar');
        $normalizer = Phake::mock('Eloquent\Pathogen\Normalizer\PathNormalizerInterface');
        Phake::when($normalizer)->normalize($className)->thenReturn($normalizedClassName);

        $this->assertSame($normalizedClassName, $className->normalize($normalizer));
    }

    public function testShortName()
    {
        $className = $this->factory->create('\foo\bar\baz');

        $this->assertSame('baz', $className->shortName()->string());
    }
}
