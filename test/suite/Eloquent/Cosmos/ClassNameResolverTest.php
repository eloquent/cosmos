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

use Eloquent\Equality\Comparator;
use PHPUnit_Framework_TestCase;

class ClassNameResolverTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $namespaceName = ClassName::fromString('\Foo\Bar');
        $usedClasses = array(
            array(
                ClassName::fromString('\Baz\Qux'),
                ClassName::fromString('Doom'),
            ),
            array(
                ClassName::fromString('\Splat\Pip'),
                ClassName::fromString('Spam'),
            ),
        );
        $comparator = new Comparator;
        $resolver = new ClassNameResolver(
            $namespaceName,
            $usedClasses,
            $comparator
        );

        $this->assertSame($namespaceName, $resolver->namespaceName());
        $this->assertSame($usedClasses, $resolver->usedClasses());
        $this->assertSame($comparator, $resolver->comparator());
    }

    public function testConstructorDefaults()
    {
        $resolver = new ClassNameResolver;

        $this->assertNull($resolver->namespaceName());
        $this->assertSame(array(), $resolver->usedClasses());
        $this->assertInstanceOf(
            'Eloquent\Equality\Comparator',
            $resolver->comparator()
        );
    }

    public function testConstructorNormalization()
    {
        $namespaceName = ClassName::fromString('Foo\Bar');
        $usedClasses = array(
            array(
                ClassName::fromString('Baz\Qux'),
                ClassName::fromString('Doom'),
            ),
            array(
                ClassName::fromString('Splat\Pip'),
                ClassName::fromString('Spam'),
            ),
        );
        $resolver = new ClassNameResolver(
            $namespaceName,
            $usedClasses
        );
        $expectedNamespaceName = ClassName::fromString('\Foo\Bar');
        $expectedUsedClasses = array(
            array(
                ClassName::fromString('\Baz\Qux'),
                ClassName::fromString('Doom'),
            ),
            array(
                ClassName::fromString('\Splat\Pip'),
                ClassName::fromString('Spam'),
            ),
        );

        $this->assertEquals($expectedNamespaceName, $resolver->namespaceName());
        $this->assertEquals($expectedUsedClasses, $resolver->usedClasses());
    }
    public function testConstructorFailureInvalidAlias()
    {
        $namespaceName = ClassName::fromString('\Foo\Bar');
        $usedClasses = array(
            array(
                ClassName::fromString('\Baz\Qux'),
                ClassName::fromString('Doom\Splat'),
            ),
        );

        $this->setExpectedException(
            __NAMESPACE__.'\Exception\InvalidUsedClassAliasException'
        );
        new ClassNameResolver(
            $namespaceName,
            $usedClasses
        );
    }

    public function resolveData()
    {
        $data = array();

        $namespaceName = null;
        $usedClasses = array();
        $className = ClassName::fromString('Foo');
        $expected = ClassName::fromString('\Foo');
        $data['Global namespace, no use statements, non-namespaced class'] = array($expected, $namespaceName, $usedClasses, $className);

        $namespaceName = null;
        $usedClasses = array();
        $className = ClassName::fromString('Foo\Bar\Baz');
        $expected = ClassName::fromString('\Foo\Bar\Baz');
        $data['Global namespace, no use statements, namespaced class'] = array($expected, $namespaceName, $usedClasses, $className);

        $namespaceName = null;
        $usedClasses = array(
            array(
                ClassName::fromString('\Bar\Baz\Qux'),
            ),
        );
        $className = ClassName::fromString('Qux');
        $expected = ClassName::fromString('\Bar\Baz\Qux');
        $data['Global namespace, use statements without aliases'] = array($expected, $namespaceName, $usedClasses, $className);

        $namespaceName = null;
        $usedClasses = array(
            array(
                ClassName::fromString('\Bar\Baz\Qux'),
                ClassName::fromString('Foo'),
            ),
        );
        $className = ClassName::fromString('Foo');
        $expected = ClassName::fromString('\Bar\Baz\Qux');
        $data['Global namespace, use statements with aliases'] = array($expected, $namespaceName, $usedClasses, $className);

        $namespaceName = ClassName::fromString('\Foo\Bar\Baz');
        $usedClasses = array();
        $className = ClassName::fromString('Qux');
        $expected = ClassName::fromString('\Foo\Bar\Baz\Qux');
        $data['Namespaced, no use statements, non-namespaced class'] = array($expected, $namespaceName, $usedClasses, $className);

        $namespaceName = ClassName::fromString('\Foo\Bar\Baz');
        $usedClasses = array();
        $className = ClassName::fromString('Qux\Doom\Splat');
        $expected = ClassName::fromString('\Foo\Bar\Baz\Qux\Doom\Splat');
        $data['Namespaced, no use statements, namespaced class'] = array($expected, $namespaceName, $usedClasses, $className);

        $namespaceName = ClassName::fromString('\Foo\Bar\Baz');
        $usedClasses = array(
            array(
                ClassName::fromString('\Qux\Doom\Splat'),
            ),
        );
        $className = ClassName::fromString('Splat');
        $expected = ClassName::fromString('\Qux\Doom\Splat');
        $data['Namespaced, use statements without aliases, non-namespaced class'] = array($expected, $namespaceName, $usedClasses, $className);

        $namespaceName = ClassName::fromString('\Foo\Bar\Baz');
        $usedClasses = array(
            array(
                ClassName::fromString('\Qux\Doom\Splat'),
                ClassName::fromString('Pip'),
            ),
        );
        $className = ClassName::fromString('Pip');
        $expected = ClassName::fromString('\Qux\Doom\Splat');
        $data['Namespaced, use statements with aliases, non-namespaced class'] = array($expected, $namespaceName, $usedClasses, $className);

        $namespaceName = null;
        $usedClasses = array();
        $className = ClassName::fromString('\Foo\Bar\Baz');
        $expected = ClassName::fromString('\Foo\Bar\Baz');
        $data['Global namespace, fully qualified class'] = array($expected, $namespaceName, $usedClasses, $className);

        $namespaceName = ClassName::fromString('\Foo\Bar\Baz');
        $usedClasses = array();
        $className = ClassName::fromString('\Qux\Doom\Splat');
        $expected = ClassName::fromString('\Qux\Doom\Splat');
        $data['Namespaced, fully qualified class'] = array($expected, $namespaceName, $usedClasses, $className);

        return $data;
    }

    /**
     * @dataProvider resolveData
     */
    public function testResolve(
        ClassName $expected,
        ClassName $namespaceName = null,
        array $usedClasses,
        $className
    ) {
        $resolver = new ClassNameResolver($namespaceName, $usedClasses);

        $this->assertEquals($expected, $resolver->resolve($className));
    }

    public function shortenData()
    {
        $data = array();

        $namespaceName = null;
        $usedClasses = array();
        $className = ClassName::fromString('Foo\Bar');
        $expected = ClassName::fromString('Foo\Bar');
        $data['Unable to shorten'] = array($expected, $namespaceName, $usedClasses, $className);

        $namespaceName = null;
        $usedClasses = array();
        $className = ClassName::fromString('\Foo\Bar');
        $expected = ClassName::fromString('\Foo\Bar');
        $data['Deal with leading slashes'] = array($expected, $namespaceName, $usedClasses, $className);

        $namespaceName = ClassName::fromString('\Foo');
        $usedClasses = array();
        $className = ClassName::fromString('\Foo\Bar');
        $expected = ClassName::fromString('Bar');
        $data['Shortened by namespace'] = array($expected, $namespaceName, $usedClasses, $className);

        $namespaceName = ClassName::fromString('\Foo');
        $usedClasses = array();
        $className = ClassName::fromString('\Foo\Bar\Baz');
        $expected = ClassName::fromString('Bar\Baz');
        $data['Shortened by sub-namespace'] = array($expected, $namespaceName, $usedClasses, $className);

        $namespaceName = ClassName::fromString('\Foo');
        $usedClasses = array(
            array(
                ClassName::fromString('\Foo\Bar\Baz'),
            ),
        );
        $className = ClassName::fromString('\Foo\Bar\Baz');
        $expected = ClassName::fromString('Baz');
        $data['Shortened by use statement without alias'] = array($expected, $namespaceName, $usedClasses, $className);

        $namespaceName = ClassName::fromString('\Foo');
        $usedClasses = array(
            array(
                ClassName::fromString('\Foo\Bar\Baz'),
                ClassName::fromString('Qux'),
            ),
        );
        $className = ClassName::fromString('\Foo\Bar\Baz');
        $expected = ClassName::fromString('Qux');
        $data['Shortened by use statement with alias'] = array($expected, $namespaceName, $usedClasses, $className);

        return $data;
    }

    /**
     * @dataProvider shortenData
     */
    public function testShorten(
        ClassName $expected,
        ClassName $namespaceName = null,
        array $usedClasses,
        $className
    ) {
        $resolver = new ClassNameResolver($namespaceName, $usedClasses);

        $this->assertEquals($expected, $resolver->shorten($className));
    }
}
