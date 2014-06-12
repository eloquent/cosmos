<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Symbol;

use Eloquent\Cosmos\Resolution\Context\ResolutionContext;
use Eloquent\Cosmos\Symbol\Factory\SymbolFactory;
use Eloquent\Cosmos\UseStatement\UseStatement;
use PHPUnit_Framework_TestCase;
use Phake;
use ReflectionClass;

class QualifiedSymbolTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->factory = new SymbolFactory;
    }

    public function createData()
    {
        //                                                 symbol                  atoms
        return array(
            'Root namespace'                      => array('\\',                   array()),
            'Qualified'                           => array('\Namespace\Symbol',    array('Namespace', 'Symbol')),
            'Qualified with empty atoms'          => array('\Namespace\\\\Symbol', array('Namespace', 'Symbol')),
            'Qualified with empty atoms at start' => array('\\\\Symbol',           array('Symbol')),
            'Qualified with empty atoms at end'   => array('\Symbol\\\\',          array('Symbol')),
        );
    }

    /**
     * @dataProvider createData
     */
    public function testFromString($symbolString, array $atoms)
    {
        $symbol = QualifiedSymbol::fromString($symbolString);

        $this->assertSame($atoms, $symbol->atoms());
        $this->assertTrue($symbol instanceof QualifiedSymbol);
    }

    public function testFromStringFailureReference()
    {
        $this->setExpectedException('Eloquent\Pathogen\Exception\NonAbsolutePathException');
        QualifiedSymbol::fromString('Symbol');
    }

    /**
     * @dataProvider createData
     */
    public function testFromAtoms($pathString, array $atoms)
    {
        $symbol = QualifiedSymbol::fromAtoms($atoms);

        $this->assertSame($atoms, $symbol->atoms());
        $this->assertTrue($symbol instanceof QualifiedSymbol);
    }

    public function fromRuntimeStringData()
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
     * @dataProvider fromRuntimeStringData
     */
    public function testFromRuntimeString($symbolString, array $atoms)
    {
        $symbol = QualifiedSymbol::fromRuntimeString($symbolString);

        $this->assertSame($atoms, $symbol->atoms());
        $this->assertTrue($symbol instanceof QualifiedSymbol);
    }

    public function testFromObject()
    {
        $symbol = QualifiedSymbol::fromString('\Symbol');

        $this->assertSame('\Eloquent\Cosmos\Symbol\QualifiedSymbol', QualifiedSymbol::fromObject($symbol)->string());
    }

    public function testFromClass()
    {
        $class = new ReflectionClass('Eloquent\Cosmos\Symbol\Factory\SymbolFactory');

        $this->assertSame(
            '\Eloquent\Cosmos\Symbol\Factory\SymbolFactory',
            QualifiedSymbol::fromClass($class)->string()
        );
    }

    public function testGlobalNamespace()
    {
        $symbol = QualifiedSymbol::globalNamespace();

        $this->assertSame(array(), $symbol->atoms());
        $this->assertTrue($symbol instanceof QualifiedSymbol);
        $this->assertSame($symbol, QualifiedSymbol::globalNamespace());
    }

    public function symbolData()
    {
        //                             symbol                  atoms
        return array(
            'Root namespace'  => array('\\',                   array()),
            'Single atom'     => array('\Symbol',              array('Symbol')),
            'Multiple atoms'  => array('\Namespace\Symbol',    array('Namespace', 'Symbol')),
            'Parent atom'     => array('\Namespace\..\Symbol', array('Namespace', '..', 'Symbol')),
            'Self atom'       => array('\Namespace\.\Symbol',  array('Namespace', '.', 'Symbol')),
        );
    }

    /**
     * @dataProvider symbolData
     */
    public function testConstructor($symbolString, array $atoms)
    {
        $symbol = $this->factory->create($symbolString);

        $this->assertSame($atoms, $symbol->atoms());
        $this->assertSame($symbolString, $symbol->string());
        $this->assertSame($symbolString, strval($symbol));
    }

    public function testConstructorFailureInvalidAtom()
    {
        $this->setExpectedException('Eloquent\Cosmos\Symbol\Exception\InvalidSymbolAtomException');

        $this->factory->create('\Namespace\Sym-bol');
    }

    public function namePartData()
    {
        //                                             symbol          name           nameWithoutExtension namePrefix nameSuffix extension
        return array(
            'Root namespace'                  => array('\\',           '',            '',                  '',        null,      null),
            'No extensions'                   => array('\foo',         'foo',         'foo',               'foo',     null,      null),
            'Empty extension'                 => array('\foo_',        'foo_',        'foo',               'foo',     '',        ''),
            'Single extension'                => array('\foo_bar',     'foo_bar',     'foo',               'foo',     'bar',     'bar'),
            'Multiple extensions'             => array('\foo_bar_baz', 'foo_bar_baz', 'foo_bar',           'foo',     'bar_baz', 'baz'),
            'No name with single extension'   => array('\_foo',        '_foo',        '',                  '',        'foo',     'foo'),
            'No name with multiple extension' => array('\_foo_bar',    '_foo_bar',    '_foo',              '',        'foo_bar', 'bar'),
        );
    }

    /**
     * @dataProvider namePartData
     */
    public function testNamePartMethods($symbolString, $name, $nameWithoutExtension, $namePrefix, $nameSuffix, $extension)
    {
        $symbol = $this->factory->create($symbolString);

        $this->assertSame($name, $symbol->name());
        $this->assertSame($nameWithoutExtension, $symbol->nameWithoutExtension());
        $this->assertSame($namePrefix, $symbol->namePrefix());
        $this->assertSame($nameSuffix, $symbol->nameSuffix());
        $this->assertSame($extension, $symbol->extension());
        $this->assertSame(null !== $extension, $symbol->hasExtension());
    }

    public function joinData()
    {
        //                                              symbol      reference  expectedResult
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
    public function testJoin($symbolString, $referenceString, $expectedResultString)
    {
        $symbol = $this->factory->create($symbolString);
        $reference = $this->factory->create($referenceString);
        $result = $symbol->join($reference);

        $this->assertSame($expectedResultString, $result->string());
    }

    public function testJoinFailureQualified()
    {
        $symbol = $this->factory->create('\foo');
        $reference = $this->factory->create('\bar');

        $this->setExpectedException('PHPUnit_Framework_Error');
        $symbol->join($reference);
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
        $symbol = $this->factory->create('\foo\..\bar');
        $normalizedSymbol = $this->factory->create('\bar');

        $this->assertEquals($normalizedSymbol, $symbol->normalize());
    }

    public function resolveAbsolutePathData()
    {
        //                                                    namespace          symbol         expectedResult
        return array(
            'Root against single atom'                => array('\\',             '\foo',        '\foo'),
            'Single atom against single atom'         => array('\foo',           '\bar',        '\bar'),
            'Multiple atoms against single atom'      => array('\foo\bar',       '\baz',        '\baz'),
            'Multiple atoms against multiple atoms'   => array('\foo\..\..\bar', '\baz\..\qux', '\baz\..\qux'),
        );
    }

    /**
     * @dataProvider resolveAbsolutePathData
     */
    public function testResolveAbsolutePaths($nameSpaceString, $symbolString, $expectedResult)
    {
        $namespace = $this->factory->create($nameSpaceString);
        $symbol = $this->factory->create($symbolString);
        $resolved = $namespace->resolve($symbol);

        $this->assertSame($expectedResult, $resolved->string());
    }

    public function resolveRelativePathData()
    {
        //                                                                                        namespace     symbol       expectedResult
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
    public function testResolveRelativePaths($nameSpaceString, $symbolString, $expectedResult)
    {
        $namespace = $this->factory->create($nameSpaceString);
        $symbol = $this->factory->create($symbolString);
        $resolved = $namespace->resolve($symbol);

        $this->assertSame($expectedResult, $resolved->string());
    }

    public function testFirstAtomAsReference()
    {
        $symbol = $this->factory->create('\foo\bar\baz');

        $this->assertSame('foo', $symbol->firstAtomAsReference()->string());
    }

    public function testLastAtomAsReference()
    {
        $symbol = $this->factory->create('\foo\bar\baz');

        $this->assertSame('baz', $symbol->lastAtomAsReference()->string());
    }

    public function relativeToContextData()
    {
        //                                           symbol                      expected
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
    public function testRelativeToContext($symbolString, $expected)
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
            $this->factory->create($symbolString)->relativeToContext($this->context)->string()
        );
    }

    public function testAccept()
    {
        $visitor = Phake::mock('Eloquent\Cosmos\Resolution\Context\ResolutionContextVisitorInterface');
        $symbol = $this->factory->create('\Foo\Bar');
        $symbol->accept($visitor);

        Phake::verify($visitor)->visitQualifiedSymbol($this->identicalTo($symbol));
    }
}
