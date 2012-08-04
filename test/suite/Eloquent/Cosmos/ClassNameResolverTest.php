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

use PHPUnit_Framework_TestCase;

class ClassNameResolverTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $usedClasses = array(
            'Baz\Qux' => 'Doom',
            'Splat\Pip' => 'Spam',
        );
        $resolver = new ClassNameResolver('Foo\Bar', $usedClasses);

        $this->assertSame('Foo\Bar', $resolver->namespaceName());
        $this->assertSame($usedClasses, $resolver->usedClasses());
    }

    public function testConstructorDefaults()
    {
        $resolver = new ClassNameResolver;

        $this->assertNull($resolver->namespaceName());
        $this->assertSame(array(), $resolver->usedClasses());
    }

    public function testNormalizeUsedClasses()
    {
        $usedClasses = array(
            'Baz\Qux' => null,
            'Doom\Splat' => null,
        );
        $resolver = new ClassNameResolver('Foo\Bar', $usedClasses);
        $expected = array(
            'Baz\Qux' => 'Qux',
            'Doom\Splat' => 'Splat',
        );

        $this->assertSame($expected, $resolver->usedClasses());
    }

    public function resolveData()
    {
        $data = array();

        // #0: Global namespace, no use statements, non-namespaced class
        $namespaceName = null;
        $usedClasses = array();
        $className = 'Foo';
        $expected = 'Foo';
        $data[] = array($expected, $namespaceName, $usedClasses, $className);

        // #1: Global namespace, no use statements, namespaced class
        $namespaceName = null;
        $usedClasses = array();
        $className = 'Foo\Bar\Baz';
        $expected = 'Foo\Bar\Baz';
        $data[] = array($expected, $namespaceName, $usedClasses, $className);

        // #2: Global namespace, use statements
        $namespaceName = null;
        $usedClasses = array(
            'Bar\Baz\Qux' => 'Foo',
        );
        $className = 'Foo';
        $expected = 'Bar\Baz\Qux';
        $data[] = array($expected, $namespaceName, $usedClasses, $className);

        // #3: Namespaced, no use statements, non-namespaced class
        $namespaceName = 'Foo\Bar\Baz';
        $usedClasses = array();
        $className = 'Qux';
        $expected = 'Foo\Bar\Baz\Qux';
        $data[] = array($expected, $namespaceName, $usedClasses, $className);

        // #4: Namespaced, no use statements, namespaced class
        $namespaceName = 'Foo\Bar\Baz';
        $usedClasses = array();
        $className = 'Qux\Doom\Splat';
        $expected = 'Foo\Bar\Baz\Qux\Doom\Splat';
        $data[] = array($expected, $namespaceName, $usedClasses, $className);

        // #5: Namespaced, use statements, non-namespaced class
        $namespaceName = 'Foo\Bar\Baz';
        $usedClasses = array(
            'Qux\Doom\Splat' => 'Pip',
        );
        $className = 'Pip';
        $expected = 'Qux\Doom\Splat';
        $data[] = array($expected, $namespaceName, $usedClasses, $className);

        // #6: Global namespace, fully qualified class
        $namespaceName = null;
        $usedClasses = array();
        $className = '\Foo\Bar\Baz';
        $expected = 'Foo\Bar\Baz';
        $data[] = array($expected, $namespaceName, $usedClasses, $className);

        // #7: Namespaced, fully qualified class
        $namespaceName = 'Foo\Bar\Baz';
        $usedClasses = array();
        $className = '\Qux\Doom\Splat';
        $expected = 'Qux\Doom\Splat';
        $data[] = array($expected, $namespaceName, $usedClasses, $className);

        return $data;
    }

    /**
     * @dataProvider resolveData
     */
    public function testResolve($expected, $namespaceName, array $usedClasses, $className)
    {
        $resolver = new ClassNameResolver($namespaceName, $usedClasses);

        $this->assertSame($expected, $resolver->resolve($className));
    }

    public function testResolveFailureInvalidClassName()
    {
        $resolver = new ClassNameResolver;

        $this->setExpectedException(__NAMESPACE__.'\Exception\InvalidClassNameException');
        $resolver->resolve('');
    }
}
