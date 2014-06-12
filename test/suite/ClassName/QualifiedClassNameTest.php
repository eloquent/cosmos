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

use Eloquent\Cosmos\Resolution\Context\ResolutionContext;
use Eloquent\Cosmos\UseStatement\UseStatement;
use Phake;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class QualifiedClassNameTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->factory = new Factory\ClassNameFactory;
    }

    public function createData()
    {
        //                                                 className              atoms
        return array(
            'Root namespace'                      => array('\\',                  array()),
            'Qualified'                           => array('\Namespace\Class',    array('Namespace', 'Class')),
            'Qualified with empty atoms'          => array('\Namespace\\\\Class', array('Namespace', 'Class')),
            'Qualified with empty atoms at start' => array('\\\\Class',           array('Class')),
            'Qualified with empty atoms at end'   => array('\Class\\\\',          array('Class')),
        );
    }

    /**
     * @dataProvider createData
     */
    public function testFromString($classNameString, array $atoms)
    {
        $className = QualifiedClassName::fromString($classNameString);

        $this->assertSame($atoms, $className->atoms());
        $this->assertTrue($className instanceof QualifiedClassName);
    }

    public function testFromStringFailureReference()
    {
        $this->setExpectedException('Eloquent\Pathogen\Exception\NonAbsolutePathException');
        QualifiedClassName::fromString('Class');
    }

    /**
     * @dataProvider createData
     */
    public function testFromAtoms($pathString, array $atoms)
    {
        $className = QualifiedClassName::fromAtoms($atoms);

        $this->assertSame($atoms, $className->atoms());
        $this->assertTrue($className instanceof QualifiedClassName);
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
        $className = QualifiedClassName::fromRuntimeString($classNameString);

        $this->assertSame($atoms, $className->atoms());
        $this->assertTrue($className instanceof QualifiedClassName);
    }

    public function testFromObject()
    {
        $className = QualifiedClassName::fromString('\Class');

        $this->assertSame(
            '\Eloquent\Cosmos\ClassName\QualifiedClassName',
            QualifiedClassName::fromObject($className)->string()
        );
    }

    public function testFromReflector()
    {
        $reflector = new ReflectionClass('Eloquent\Cosmos\ClassName\Factory\ClassNameFactory');

        $this->assertSame(
            '\Eloquent\Cosmos\ClassName\Factory\ClassNameFactory',
            QualifiedClassName::fromReflector($reflector)->string()
        );
    }

    public function testGlobalNamespace()
    {
        $className = QualifiedClassName::globalNamespace();

        $this->assertSame(array(), $className->atoms());
        $this->assertTrue($className instanceof QualifiedClassName);
        $this->assertSame($className, QualifiedClassName::globalNamespace());
    }

    public function classNameData()
    {
        //                             className              atoms
        return array(
            'Root namespace'  => array('\\',                  array()),
            'Single atom'     => array('\Class',              array('Class')),
            'Multiple atoms'  => array('\Namespace\Class',    array('Namespace', 'Class')),
            'Parent atom'     => array('\Namespace\..\Class', array('Namespace', '..', 'Class')),
            'Self atom'       => array('\Namespace\.\Class',  array('Namespace', '.', 'Class')),
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

        $this->factory->create('\Namespace\Class-Name');
    }

    public function namePartData()
    {
        //                                             className         name           nameWithoutExtension  namePrefix  nameSuffix  extension
        return array(
            'Root namespace'                  => array('\\',            '',             '',                   '',         null,       null),
            'No extensions'                   => array('\foo',          'foo',          'foo',                'foo',      null,       null),
            'Empty extension'                 => array('\foo_',         'foo_',         'foo',                'foo',      '',         ''),
            'Single extension'                => array('\foo_bar',      'foo_bar',      'foo',                'foo',      'bar',      'bar'),
            'Multiple extensions'             => array('\foo_bar_baz',  'foo_bar_baz',  'foo_bar',            'foo',      'bar_baz',  'baz'),
            'No name with single extension'   => array('\_foo',         '_foo',         '',                   '',         'foo',      'foo'),
            'No name with multiple extension' => array('\_foo_bar',     '_foo_bar',     '_foo',               '',         'foo_bar',  'bar'),
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
        //                                              className   reference  expectedResult
        return array(
            'Root namespace'                   => array('\\',       'foo',     '\foo'),
            'Single atom'                      => array('\foo',     'bar',     '\foo\bar'),
            'Multiple atoms'                   => array('\foo',     'bar\baz', '\foo\bar\baz'),
            'Multiple atoms to multiple atoms' => array('\foo\bar', 'baz\qux', '\foo\bar\baz\qux'),
            'Special atoms'                    => array('\foo',     '.\..',    '\foo\.\..'),
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
        $className = $this->factory->create('\foo');
        $reference = $this->factory->create('\bar');

        $this->setExpectedException('PHPUnit_Framework_Error');
        $className->join($reference);
    }

    public function relativeToData()
    {
        //                                        parent               child                expectedResult
        return array(
            'Self'                       => array('\foo',              '\foo',              '.'),
            'Child'                      => array('\foo',              '\foo\bar',          'bar'),
            'Ancestor'                   => array('\foo',              '\foo\bar\baz',      'bar\baz'),
            'Sibling'                    => array('\foo',              '\bar',              '..\bar'),
            'Parent\'s sibling'          => array('\foo\bar\baz',      '\foo\qux',          '..\..\qux'),
            'Parent\'s sibling\'s child' => array('\foo\bar\baz',      '\foo\qux\doom',     '..\..\qux\doom'),
            'Completely unrelated'       => array('\foo\bar\baz',      '\qux\doom',         '..\..\..\qux\doom'),
            'Lengthly unrelated child'   => array('\foo\bar',          '\baz\qux\doom',     '..\..\baz\qux\doom'),
            'Common suffix'              => array('\foo\bar\baz\doom', '\foo\bar\qux\doom', '..\..\qux\doom'),
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
        $className = $this->factory->create('\foo\..\bar');
        $normalizedClassName = $this->factory->create('\bar');

        $this->assertEquals($normalizedClassName, $className->normalize());
    }

    public function resolveAbsolutePathData()
    {
        //                                                    namespace            className        expectedResult
        return array(
            'Root against single atom'                => array('\\',               '\foo',          '\foo'),
            'Single atom against single atom'         => array('\foo',             '\bar',          '\bar'),
            'Multiple atoms against single atom'      => array('\foo\bar',         '\baz',          '\baz'),
            'Multiple atoms against multiple atoms'   => array('\foo\..\..\bar',   '\baz\..\qux',   '\baz\..\qux'),
        );
    }

    /**
     * @dataProvider resolveAbsolutePathData
     */
    public function testResolveAbsolutePaths($nameSpaceString, $classNameString, $expectedResult)
    {
        $namespace = $this->factory->create($nameSpaceString);
        $className = $this->factory->create($classNameString);
        $resolved = $namespace->resolve($className);

        $this->assertSame($expectedResult, $resolved->string());
    }

    public function resolveRelativePathData()
    {
        //                                                                                        namespace     className    expectedResult
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
     * @dataProvider resolveRelativePathData
     */
    public function testResolveRelativePaths($nameSpaceString, $classNameString, $expectedResult)
    {
        $namespace = $this->factory->create($nameSpaceString);
        $className = $this->factory->create($classNameString);
        $resolved = $namespace->resolve($className);

        $this->assertSame($expectedResult, $resolved->string());
    }

    public function testShortName()
    {
        $className = $this->factory->create('\foo\bar\baz');

        $this->assertSame('baz', $className->shortName()->string());
    }

    public function testFirstAtomShortName()
    {
        $className = $this->factory->create('\foo\bar\baz');

        $this->assertSame('foo', $className->firstAtomShortName()->string());
    }

    public function relativeToContextData()
    {
        //                                           className                   expected
        return array(
            'Primary namespace +1'          => array('\Foo\Bar\Baz',             'Baz'),
            'Primary namespace +2'          => array('\Foo\Bar\Baz\Qux',         'Baz\Qux'),
            'Primary namespace +3'          => array('\Foo\Bar\Baz\Qux\Doom',    'Baz\Qux\Doom'),
            'Use statement'                 => array('\Baz\Qux',                 'Qux'),
            'Use statement +1'              => array('\Baz\Qux\Doom',            'Qux\Doom'),
            'Use statement +2'              => array('\Baz\Qux\Doom\Splat',      'Qux\Doom\Splat'),
            'Alias'                         => array('\Doom\Splat',              'Ping'),
            'Alias +1'                      => array('\Doom\Splat\Pong',         'Ping\Pong'),
            'Alias +2'                      => array('\Doom\Splat\Pong\Pang',    'Ping\Pong\Pang'),
            'Shortest use statement'        => array('\Pong\Pang\Peng',          'Peng'),
            'Use statement not too short'   => array('\Pong\Pang\Ping',          'Pang\Ping'),
            'No relevant statements'        => array('\Zing\Zang\Zong',          '\Zing\Zang\Zong'),
            'Avoid use statement clash'     => array('\Foo\Bar\Qux',             'namespace\Qux'),
            'Avoid use statement clash + N' => array('\Foo\Bar\Qux\Doom\Splat',  'namespace\Qux\Doom\Splat'),
            'Avoid use alias clash'         => array('\Foo\Bar\Ping',            'namespace\Ping'),
            'Avoid use alias clash + N'     => array('\Foo\Bar\Ping\Doom\Splat', 'namespace\Ping\Doom\Splat'),
        );
    }

    /**
     * @dataProvider relativeToContextData
     */
    public function testRelativeToContext($classNameString, $expected)
    {
        $this->primaryNamespace = $this->factory->create('\Foo\Bar');
        $this->useStatements = array(
            new UseStatement($this->factory->create('\Baz\Qux')),
            new UseStatement($this->factory->create('\Doom\Splat'), $this->factory->create('Ping')),
            new UseStatement($this->factory->create('\Pong\Pang')),
            new UseStatement($this->factory->create('\Pong\Pang\Peng')),
        );
        $this->context = new ResolutionContext($this->primaryNamespace, $this->useStatements, $this->factory);

        $this->assertSame(
            $expected,
            $this->factory->create($classNameString)->relativeToContext($this->context)->string()
        );
    }

    public function testAccept()
    {
        $visitor = Phake::mock('Eloquent\Cosmos\Resolution\Context\ResolutionContextVisitorInterface');
        $className = $this->factory->create('\Foo\Bar');
        $className->accept($visitor);

        Phake::verify($visitor)->visitQualifiedClassName($this->identicalTo($className));
    }
}
