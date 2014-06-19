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

class SymbolReferenceTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->factory = new SymbolFactory;

        $this->primaryNamespace = $this->factory->create('\VendorA\PackageA');
        $this->useStatements = array(
            UseStatement::create($this->factory->create('\VendorB\PackageB')),
            UseStatement::create($this->factory->create('\VendorC\PackageC')),
        );
        $this->context = new ResolutionContext($this->primaryNamespace, $this->useStatements, $this->factory);
    }

    public function createData()
    {
        //                                                 symbol                  atoms
        return array(
            'Empty'                               => array('',                     array('.')),
            'Self'                                => array('.',                    array('.')),
            'Reference'                           => array('Namespace\Symbol',     array('Namespace', 'Symbol')),
            'Reference with trailing separator'   => array('Namespace\Symbol\\',   array('Namespace', 'Symbol')),
            'Reference with empty atoms'          => array('Namespace\\\\Symbol',  array('Namespace', 'Symbol')),
            'Reference with empty atoms at end'   => array('Namespace\Symbol\\\\', array('Namespace', 'Symbol')),
        );
    }

    /**
     * @dataProvider createData
     */
    public function testFromString($symbolString, array $atoms)
    {
        $symbol = SymbolReference::fromString($symbolString);

        $this->assertSame($atoms, $symbol->atoms());
        $this->assertTrue($symbol instanceof SymbolReference);
    }

    public function testFromStringFailureQualified()
    {
        $this->setExpectedException('Eloquent\Pathogen\Exception\NonRelativePathException');
        SymbolReference::fromString('\Symbol');
    }

    /**
     * @dataProvider createData
     */
    public function testFromAtoms($pathString, array $atoms)
    {
        $symbol = SymbolReference::fromAtoms($atoms);

        $this->assertSame($atoms, $symbol->atoms());
        $this->assertTrue($symbol instanceof SymbolReference);
    }

    public function symbolData()
    {
        //                             symbol                 atoms
        return array(
            'Self'            => array('.',                   array('.')),
            'Single atom'     => array('Symbol',              array('Symbol')),
            'Multiple atoms'  => array('Namespace\Symbol',    array('Namespace', 'Symbol')),
            'Parent atom'     => array('Namespace\..\Symbol', array('Namespace', '..', 'Symbol')),
            'Self atom'       => array('Namespace\.\Symbol',  array('Namespace', '.', 'Symbol')),
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

    public function testConstructorEmpty()
    {
        $this->assertSame('.', $this->factory->create('')->string());
    }

    public function testConstructorFailureInvalidAtom()
    {
        $this->setExpectedException('Eloquent\Cosmos\Symbol\Exception\InvalidSymbolAtomException');

        $this->factory->create('Namespace\Sym-Bol');
    }

    public function namePartData()
    {
        //                                             symbol         name           nameWithoutExtension namePrefix nameSuffix extension
        return array(
            'No extensions'                   => array('foo',         'foo',         'foo',               'foo',     null,      null),
            'Empty extension'                 => array('foo_',        'foo_',        'foo',               'foo',     '',        ''),
            'Single extension'                => array('foo_bar',     'foo_bar',     'foo',               'foo',     'bar',     'bar'),
            'Multiple extensions'             => array('foo_bar_baz', 'foo_bar_baz', 'foo_bar',           'foo',     'bar_baz', 'baz'),
            'No name with single extension'   => array('_foo',        '_foo',        '',                  '',        'foo',     'foo'),
            'No name with multiple extension' => array('_foo_bar',    '_foo_bar',    '_foo',              '',        'foo_bar', 'bar'),
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
        //                                              symbol     reference  expectedResult
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
    public function testJoin($symbolString, $referenceString, $expectedResultString)
    {
        $symbol = $this->factory->create($symbolString);
        $reference = $this->factory->create($referenceString);
        $result = $symbol->join($reference);

        $this->assertSame($expectedResultString, $result->string());
    }

    public function testJoinFailureQualified()
    {
        $symbol = $this->factory->create('foo');
        $reference = $this->factory->create('\bar');

        $this->setExpectedException('PHPUnit_Framework_Error');
        $symbol->join($reference);
    }

    public function testNormalize()
    {
        $symbol = $this->factory->create('foo\..\bar');
        $normalizedSymbol = $this->factory->create('bar');

        $this->assertEquals($normalizedSymbol, $symbol->normalize());
    }

    public function resolveAgainstData()
    {
        //                                                                                        namespace      symbol      expectedResult
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
    public function testResolveAgainstRelativePaths($namespaceString, $symbolString, $expectedResult)
    {
        $namespace = $this->factory->create($namespaceString);
        $symbol = $this->factory->create($symbolString);
        $resolved = $symbol->resolveAgainst($namespace);

        $this->assertSame($expectedResult, $resolved->string());
    }

    public function testLastAtomAsReference()
    {
        $symbol = $this->factory->create('foo\bar\baz');

        $this->assertSame('baz', $symbol->lastAtomAsReference()->string());
    }

    public function testLastAtomAsReferenceUnchanged()
    {
        $symbol = $this->factory->create('foo');

        $this->assertSame($symbol, $symbol->lastAtomAsReference());
    }

    public function testFirstAtomAsReference()
    {
        $symbol = $this->factory->create('foo\bar\baz');

        $this->assertSame('foo', $symbol->firstAtomAsReference()->string());
    }

    public function testFirstAtomAsReferenceUnchanged()
    {
        $symbol = $this->factory->create('foo');

        $this->assertSame($symbol, $symbol->firstAtomAsReference());
    }

    public function testResolveAgainstContext()
    {
        $reference = $this->factory->create('Symbol');

        $this->assertSame('\VendorA\PackageA\Symbol', $reference->resolveAgainstContext($this->context)->string());
    }

    public function testResolveAgainstContextGlobalNsNoUseStatements()
    {
        $this->context = new ResolutionContext;

        $this->assertSame('\Symbol', $this->factory->create('Symbol')->resolveAgainstContext($this->context)->string());
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
                UseStatement::create($this->factory->create('\My\Full\Classname'), $this->factory->create('Another')),
                UseStatement::create($this->factory->create('\My\Full\NSname')),
                UseStatement::create($this->factory->create('\ArrayObject')),
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
            '\VendorA\PackageA\.\PackageB\Symbol',
            $this->factory->create('.\PackageB\Symbol')->resolveAgainstContext($this->context)->string()
        );
        $this->assertSame(
            '\VendorA\PackageA\..\PackageD\Symbol',
            $this->factory->create('..\PackageD\Symbol')->resolveAgainstContext($this->context)->string()
        );
        $this->assertSame(
            '\VendorB\PackageB\..\PackageD\Symbol',
            $this->factory->create('PackageB\..\PackageD\Symbol')->resolveAgainstContext($this->context)->string()
        );
    }

    public function testAccept()
    {
        $visitor = Phake::mock('Eloquent\Cosmos\Resolution\Context\ResolutionContextVisitorInterface');
        $symbol = $this->factory->create('Foo\Bar');
        $symbol->accept($visitor);

        Phake::verify($visitor)->visitSymbolReference($this->identicalTo($symbol));
    }
}
