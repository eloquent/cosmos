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

use Eloquent\Cosmos\Resolution\ResolutionContext;
use Eloquent\Cosmos\UseStatement\UseStatement;
use PHPUnit_Framework_TestCase;

class ClassNameReferenceTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->factory = new Factory\ClassNameFactory;

        $this->primaryNamespace = $this->factory->create('\VendorA\PackageA');
        $this->useStatements = array(
            new UseStatement($this->factory->create('\VendorB\PackageB')),
            new UseStatement($this->factory->create('\VendorC\PackageC')),
        );
        $this->context = new ResolutionContext($this->primaryNamespace, $this->useStatements, $this->factory);
    }

    public function createData()
    {
        //                                                 className              atoms
        return array(
            'Empty'                               => array('',                    array('.')),
            'Self'                                => array('.',                   array('.')),
            'Reference'                           => array('Namespace\Class',     array('Namespace', 'Class')),
            'Reference with trailing separator'   => array('Namespace\Class\\',   array('Namespace', 'Class')),
            'Reference with empty atoms'          => array('Namespace\\\\Class',  array('Namespace', 'Class')),
            'Reference with empty atoms at end'   => array('Namespace\Class\\\\', array('Namespace', 'Class')),
        );
    }

    /**
     * @dataProvider createData
     */
    public function testFromString($classNameString, array $atoms)
    {
        $className = ClassNameReference::fromString($classNameString);

        $this->assertSame($atoms, $className->atoms());
        $this->assertTrue($className instanceof ClassNameReference);
    }

    public function testFromStringFailureQualified()
    {
        $this->setExpectedException('Eloquent\Pathogen\Exception\NonRelativePathException');
        ClassNameReference::fromString('\Class');
    }

    /**
     * @dataProvider createData
     */
    public function testFromAtoms($pathString, array $atoms)
    {
        $className = ClassNameReference::fromAtoms($atoms);

        $this->assertSame($atoms, $className->atoms());
        $this->assertTrue($className instanceof ClassNameReference);
    }

    public function classNameData()
    {
        //                             className             atoms
        return array(
            'Self'            => array('.',                  array('.')),
            'Single atom'     => array('Class',              array('Class')),
            'Multiple atoms'  => array('Namespace\Class',    array('Namespace', 'Class')),
            'Parent atom'     => array('Namespace\..\Class', array('Namespace', '..', 'Class')),
            'Self atom'       => array('Namespace\.\Class',  array('Namespace', '.', 'Class')),
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

    public function testConstructorEmpty()
    {
        $this->assertSame('.', $this->factory->create('')->string());
    }

    public function testConstructorFailureInvalidAtom()
    {
        $this->setExpectedException(__NAMESPACE__ . '\Exception\InvalidClassNameAtomException');

        $this->factory->create('Namespace\Class-Name');
    }

    public function namePartData()
    {
        //                                             className       name            nameWithoutExtension  namePrefix  nameSuffix  extension
        return array(
            'No extensions'                   => array('foo',          'foo',          'foo',                'foo',      null,       null),
            'Empty extension'                 => array('foo_',         'foo_',         'foo',                'foo',      '',         ''),
            'Single extension'                => array('foo_bar',      'foo_bar',      'foo',                'foo',      'bar',      'bar'),
            'Multiple extensions'             => array('foo_bar_baz',  'foo_bar_baz',  'foo_bar',            'foo',      'bar_baz',  'baz'),
            'No name with single extension'   => array('_foo',         '_foo',         '',                   '',         'foo',      'foo'),
            'No name with multiple extension' => array('_foo_bar',     '_foo_bar',     '_foo',               '',         'foo_bar',  'bar'),
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
        //                                              className  reference  expectedResult
        return array(
            'Single atom'                      => array('foo',     'bar',     'foo\bar'),
            'Multiple atoms'                   => array('foo',     'bar\baz', 'foo\bar\baz'),
            'Multiple atoms to multiple atoms' => array('foo\bar', 'baz\qux', 'foo\bar\baz\qux'),
            'Special atoms'                    => array('foo',     '.\..',    'foo\.\..'),
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
        $className = $this->factory->create('foo');
        $reference = $this->factory->create('\bar');

        $this->setExpectedException('PHPUnit_Framework_Error');
        $className->join($reference);
    }

    public function testNormalize()
    {
        $className = $this->factory->create('foo\..\bar');
        $normalizedClassName = $this->factory->create('bar');

        $this->assertEquals($normalizedClassName, $className->normalize());
    }

    public function resolveAgainstData()
    {
        //                                                                                        namespace      className   expectedResult
        return array(
            'Root against single atom'                                                   => array('\\',         'foo',       '\foo'),
            'Single atom against single atom'                                            => array('\foo',       'bar',       '\foo\bar'),
            'Multiple atoms against single atom'                                         => array('\foo\bar',   'baz',       '\foo\bar\baz'),
            'Multiple atoms with slash against single atoms'                             => array('\foo\bar\\', 'baz',       '\foo\bar\baz'),
            'Multiple atoms against multiple atoms'                                      => array('\foo\bar',   'baz\qux',   '\foo\bar\baz\qux'),
            'Multiple atoms with slash against multiple atoms'                           => array('\foo\bar\\', 'baz\qux',   '\foo\bar\baz\qux'),
            'Multiple atoms with slash against multiple atoms with slash'                => array('\foo\bar\\', 'baz\qux\\', '\foo\bar\baz\qux'),
            'Root against parent atom'                                                   => array('\\',         '..',        '\..'),
            'Single atom against parent atom'                                            => array('\foo',       '..',        '\foo\..'),
            'Single atom with slash against parent atom'                                 => array('\foo\\',     '..',        '\foo\..'),
            'Single atom with slash against parent atom with slash'                      => array('\foo\\',     '..\\',      '\foo\..'),
            'Multiple atoms against parent and single atom'                              => array('\foo\bar',   '..\baz',    '\foo\bar\..\baz'),
            'Multiple atoms with slash against parent atom and single atom'              => array('\foo\bar\\', '..\baz',    '\foo\bar\..\baz'),
            'Multiple atoms with slash against parent atom and single atom with slash'   => array('\foo\bar\\', '..\baz\\',  '\foo\bar\..\baz'),
        );
    }

    /**
     * @dataProvider resolveAgainstData
     */
    public function testResolveAgainstRelativePaths($namespaceString, $classNameString, $expectedResult)
    {
        $namespace = $this->factory->create($namespaceString);
        $className = $this->factory->create($classNameString);
        $resolved = $className->resolveAgainst($namespace);

        $this->assertSame($expectedResult, $resolved->string());
    }

    public function testShortName()
    {
        $className = $this->factory->create('foo\bar\baz');

        $this->assertSame('baz', $className->shortName()->string());
    }

    public function testShortNameUnchanged()
    {
        $className = $this->factory->create('foo');

        $this->assertSame($className, $className->shortName());
    }

    public function testFirstAtomShortName()
    {
        $className = $this->factory->create('foo\bar\baz');

        $this->assertSame('foo', $className->firstAtomShortName()->string());
    }

    public function testFirstAtomShortNameUnchanged()
    {
        $className = $this->factory->create('foo');

        $this->assertSame($className, $className->firstAtomShortName());
    }

    public function testResolveAgainstContext()
    {
        $reference = $this->factory->create('Class');

        $this->assertSame(
            '\VendorA\PackageA\Class',
            $reference->resolveAgainstContext($this->context)->string()
        );
    }

    public function testResolveAgainstContextGlobalNsNoUseStatements()
    {
        $this->context = new ResolutionContext;

        $this->assertSame('\Class', $this->factory->create('Class')->resolveAgainstContext($this->context)->string());
        $this->assertSame(
            '\Vendor\Package',
            $this->factory->create('Vendor\Package')->resolveAgainstContext($this->context)->string()
        );
    }

    /**
     * @link http://php.net/manual/en/language.namespaces.importing.php
     */
    public function testResolveAgainstContextDocumentationExamples()
    {
        $this->context = new ResolutionContext(
            $this->factory->create('\foo'),
            array(
                new UseStatement(
                    $this->factory->create('\My\Full\Classname'),
                    $this->factory->create('Another')
                ),
                new UseStatement($this->factory->create('\My\Full\NSname')),
                new UseStatement($this->factory->create('\ArrayObject')),
            )
        );

        $this->assertSame(
            '\foo\Another',
            $this->factory->create('namespace\Another')->resolveAgainstContext($this->context)->string()
        );
        $this->assertSame(
            '\My\Full\Classname',
            $this->factory->create('Another')->resolveAgainstContext($this->context)->string()
        );
        $this->assertSame(
            '\My\Full\Classname\thing',
            $this->factory->create('Another\thing')->resolveAgainstContext($this->context)->string()
        );
        $this->assertSame(
            '\My\Full\NSname\subns',
            $this->factory->create('NSname\subns')->resolveAgainstContext($this->context)->string()
        );
        $this->assertSame(
            '\ArrayObject',
            $this->factory->create('ArrayObject')->resolveAgainstContext($this->context)->string()
        );
    }

    public function testResolveAgainstContextSpecialAtoms()
    {
        $this->assertSame(
            '\VendorA\PackageA\.\PackageB\Class',
            $this->factory->create('.\PackageB\Class')->resolveAgainstContext($this->context)->string()
        );
        $this->assertSame(
            '\VendorA\PackageA\..\PackageD\Class',
            $this->factory->create('..\PackageD\Class')->resolveAgainstContext($this->context)->string()
        );
        $this->assertSame(
            '\VendorB\PackageB\..\PackageD\Class',
            $this->factory->create('PackageB\..\PackageD\Class')->resolveAgainstContext($this->context)->string()
        );
    }
}
