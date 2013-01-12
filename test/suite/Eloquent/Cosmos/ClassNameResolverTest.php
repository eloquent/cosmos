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

        $namespaceName = null;
        $usedClasses = array();
        $className = 'Foo';
        $expected = 'Foo';
        $data['Global namespace, no use statements, non-namespaced class'] = array($expected, $namespaceName, $usedClasses, $className);

        $namespaceName = null;
        $usedClasses = array();
        $className = 'Foo\Bar\Baz';
        $expected = 'Foo\Bar\Baz';
        $data['Global namespace, no use statements, namespaced class'] = array($expected, $namespaceName, $usedClasses, $className);

        $namespaceName = null;
        $usedClasses = array(
            'Bar\Baz\Qux' => null,
        );
        $className = 'Qux';
        $expected = 'Bar\Baz\Qux';
        $data['Global namespace, use statements without aliases'] = array($expected, $namespaceName, $usedClasses, $className);

        $namespaceName = null;
        $usedClasses = array(
            'Bar\Baz\Qux' => 'Foo',
        );
        $className = 'Foo';
        $expected = 'Bar\Baz\Qux';
        $data['Global namespace, use statements with aliases'] = array($expected, $namespaceName, $usedClasses, $className);

        $namespaceName = 'Foo\Bar\Baz';
        $usedClasses = array();
        $className = 'Qux';
        $expected = 'Foo\Bar\Baz\Qux';
        $data['Namespaced, no use statements, non-namespaced class'] = array($expected, $namespaceName, $usedClasses, $className);

        $namespaceName = 'Foo\Bar\Baz';
        $usedClasses = array();
        $className = 'Qux\Doom\Splat';
        $expected = 'Foo\Bar\Baz\Qux\Doom\Splat';
        $data['Namespaced, no use statements, namespaced class'] = array($expected, $namespaceName, $usedClasses, $className);

        $namespaceName = 'Foo\Bar\Baz';
        $usedClasses = array(
            'Qux\Doom\Splat' => null,
        );
        $className = 'Splat';
        $expected = 'Qux\Doom\Splat';
        $data['Namespaced, use statements without aliases, non-namespaced class'] = array($expected, $namespaceName, $usedClasses, $className);

        $namespaceName = 'Foo\Bar\Baz';
        $usedClasses = array(
            'Qux\Doom\Splat' => 'Pip',
        );
        $className = 'Pip';
        $expected = 'Qux\Doom\Splat';
        $data['Namespaced, use statements with aliases, non-namespaced class'] = array($expected, $namespaceName, $usedClasses, $className);

        $namespaceName = null;
        $usedClasses = array();
        $className = '\Foo\Bar\Baz';
        $expected = 'Foo\Bar\Baz';
        $data['Global namespace, fully qualified class'] = array($expected, $namespaceName, $usedClasses, $className);

        $namespaceName = 'Foo\Bar\Baz';
        $usedClasses = array();
        $className = '\Qux\Doom\Splat';
        $expected = 'Qux\Doom\Splat';
        $data['Namespaced, fully qualified class'] = array($expected, $namespaceName, $usedClasses, $className);

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

    public function shortenData()
    {
        $data = array();

        $namespaceName = null;
        $usedClasses = array();
        $className = 'Foo\Bar';
        $expected = '\Foo\Bar';
        $data['Unable to shorten'] = array($expected, $namespaceName, $usedClasses, $className);

        $namespaceName = null;
        $usedClasses = array();
        $className = '\Foo\Bar';
        $expected = '\Foo\Bar';
        $data['Deal with leading slashes'] = array($expected, $namespaceName, $usedClasses, $className);

        $namespaceName = 'Foo';
        $usedClasses = array();
        $className = 'Foo\Bar';
        $expected = 'Bar';
        $data['Shortened by namespace'] = array($expected, $namespaceName, $usedClasses, $className);

        $namespaceName = 'Foo';
        $usedClasses = array();
        $className = 'Foo\Bar\Baz';
        $expected = 'Bar\Baz';
        $data['Shortened by sub-namespace'] = array($expected, $namespaceName, $usedClasses, $className);

        $namespaceName = 'Foo';
        $usedClasses = array(
            'Foo\Bar\Baz' => null,
        );
        $className = 'Foo\Bar\Baz';
        $expected = 'Baz';
        $data['Shortened by use statement without alias'] = array($expected, $namespaceName, $usedClasses, $className);

        $namespaceName = 'Foo';
        $usedClasses = array(
            'Foo\Bar\Baz' => 'Qux',
        );
        $className = 'Foo\Bar\Baz';
        $expected = 'Qux';
        $data['Shortened by use statement with alias'] = array($expected, $namespaceName, $usedClasses, $className);

        return $data;
    }

    /**
     * @dataProvider shortenData
     */
    public function testShorten($expected, $namespaceName, array $usedClasses, $className)
    {
        $resolver = new ClassNameResolver($namespaceName, $usedClasses);

        $this->assertSame($expected, $resolver->shorten($className));
    }

    public function testShortenFailureInvalidClassName()
    {
        $resolver = new ClassNameResolver;

        $this->setExpectedException(__NAMESPACE__.'\Exception\InvalidClassNameException');
        $resolver->shorten('');
    }
}
