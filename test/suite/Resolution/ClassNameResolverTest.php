<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution;

use Eloquent\Cosmos\ClassName\Factory\ClassNameFactory;
use Eloquent\Cosmos\UseStatement\UseStatement;
use Eloquent\Liberator\Liberator;
use PHPUnit_Framework_TestCase;

class ClassNameResolverTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->contextFactory = new ResolutionContextFactory;
        $this->resolver = new ClassNameResolver($this->contextFactory);

        $this->classNameFactory = new ClassNameFactory;

        $this->primaryNamespace = $this->classNameFactory->create('\VendorA\PackageA');
        $this->useStatements = array(
            new UseStatement($this->classNameFactory->create('\VendorB\PackageB')),
            new UseStatement($this->classNameFactory->create('\VendorC\PackageC')),
        );
        $this->context = new ResolutionContext($this->primaryNamespace, $this->useStatements, $this->classNameFactory);
    }

    public function testConstructor()
    {
        $this->assertSame($this->contextFactory, $this->resolver->contextFactory());
    }

    public function testConstructorDefaults()
    {
        $this->resolver = new ClassNameResolver;

        $this->assertSame(ResolutionContextFactory::instance(), $this->resolver->contextFactory());
    }

    public function testResolve()
    {
        $qualified = $this->classNameFactory->create('\VendorB\PackageB');
        $reference = $this->classNameFactory->create('Class');

        $this->assertSame($qualified, $this->resolver->resolve($this->primaryNamespace, $qualified));
        $this->assertSame(
            '\VendorA\PackageA\Class',
            $this->resolver->resolve($this->primaryNamespace, $reference)->string()
        );
    }

    public function testResolveAgainstContext()
    {
        $qualified = $this->classNameFactory->create('\VendorB\PackageB');
        $reference = $this->classNameFactory->create('Class');

        $this->assertSame($qualified, $this->resolver->resolveAgainstContext($this->context, $qualified));
        $this->assertSame(
            '\VendorA\PackageA\Class',
            $this->resolver->resolveAgainstContext($this->context, $reference)->string()
        );
    }

    public function testResolveAgainstContextGlobalNsNoUseStatements()
    {
        $this->context = new ResolutionContext;

        $this->assertSame(
            '\Class',
            $this->resolver->resolveAgainstContext($this->context, $this->classNameFactory->create('Class'))->string()
        );
        $this->assertSame(
            '\Vendor\Package',
            $this->resolver->resolveAgainstContext($this->context, $this->classNameFactory->create('Vendor\Package'))
                ->string()
        );
    }

    /**
     * @link http://php.net/manual/en/language.namespaces.importing.php
     */
    public function testResolveAgainstContextDocumentationExamples()
    {
        $this->context = new ResolutionContext(
            $this->classNameFactory->create('\foo'),
            array(
                new UseStatement(
                    $this->classNameFactory->create('\My\Full\Classname'),
                    $this->classNameFactory->create('Another')
                ),
                new UseStatement($this->classNameFactory->create('\My\Full\NSname')),
                new UseStatement($this->classNameFactory->create('\ArrayObject')),
            )
        );

        $this->assertSame(
            '\foo\Another',
            $this->resolver->resolveAgainstContext($this->context, $this->classNameFactory->create('namespace\Another'))
                ->string()
        );
        $this->assertSame(
            '\My\Full\Classname',
            $this->resolver->resolveAgainstContext($this->context, $this->classNameFactory->create('Another'))->string()
        );
        $this->assertSame(
            '\My\Full\Classname\thing',
            $this->resolver->resolveAgainstContext($this->context, $this->classNameFactory->create('Another\thing'))
                ->string()
        );
        $this->assertSame(
            '\My\Full\NSname\subns',
            $this->resolver->resolveAgainstContext($this->context, $this->classNameFactory->create('NSname\subns'))
                ->string()
        );
        $this->assertSame(
            '\ArrayObject',
            $this->resolver->resolveAgainstContext($this->context, $this->classNameFactory->create('ArrayObject'))
                ->string()
        );
    }

    public function testResolveAgainstContextSpecialAtoms()
    {
        $this->assertSame(
            '\VendorA\PackageA\.\PackageB\Class',
            $this->resolver->resolveAgainstContext($this->context, $this->classNameFactory->create('.\PackageB\Class'))
                ->string()
        );
        $this->assertSame(
            '\VendorA\PackageA\..\PackageD\Class',
            $this->resolver->resolveAgainstContext($this->context, $this->classNameFactory->create('..\PackageD\Class'))
                ->string()
        );
        $this->assertSame(
            '\VendorB\PackageB\..\PackageD\Class',
            $this->resolver
                ->resolveAgainstContext($this->context, $this->classNameFactory->create('PackageB\..\PackageD\Class'))
                ->string()
        );
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
        $this->primaryNamespace = $this->classNameFactory->create('\Foo\Bar');
        $this->useStatements = array(
            new UseStatement($this->classNameFactory->create('\Baz\Qux')),
            new UseStatement($this->classNameFactory->create('\Doom\Splat'), $this->classNameFactory->create('Ping')),
            new UseStatement($this->classNameFactory->create('\Pong\Pang')),
            new UseStatement($this->classNameFactory->create('\Pong\Pang\Peng')),
        );
        $this->context = new ResolutionContext($this->primaryNamespace, $this->useStatements, $this->classNameFactory);

        $this->assertSame(
            $expected,
            $this->resolver->relativeToContext($this->context, $this->classNameFactory->create($classNameString))
                ->string()
        );
    }

    public function testInstance()
    {
        $class = Liberator::liberateClass(__NAMESPACE__ . '\ClassNameResolver');
        $class->instance = null;
        $actual = ClassNameResolver::instance();

        $this->assertInstanceOf(__NAMESPACE__ . '\ClassNameResolver', $actual);
        $this->assertSame($actual, ClassNameResolver::instance());
    }
}
