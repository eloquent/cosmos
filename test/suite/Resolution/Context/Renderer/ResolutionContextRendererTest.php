<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Context\Renderer;

use Eloquent\Cosmos\ClassName\ClassName;
use Eloquent\Cosmos\Resolution\Context\ResolutionContext;
use Eloquent\Cosmos\UseStatement\UseStatement;
use Eloquent\Liberator\Liberator;
use PHPUnit_Framework_TestCase;

class ResolutionContextRendererTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->renderer = new ResolutionContextRenderer;
    }

    public function testRenderContext()
    {
        $context = new ResolutionContext(
            ClassName::fromString('\NamespaceA\NamespaceB'),
            array(
                new UseStatement(ClassName::fromString('\NamespaceC\NamespaceD\ClassA')),
                new UseStatement(
                    ClassName::fromString('\NamespaceE\NamespaceF\ClassB'),
                    ClassName::fromString('ClassC')
                ),
                new UseStatement(ClassName::fromString('\ClassD')),
                new UseStatement(ClassName::fromString('\ClassE'), ClassName::fromString('ClassF')),
            )
        );
        $expected = <<<'EOD'
namespace NamespaceA\NamespaceB;

use NamespaceC\NamespaceD\ClassA;
use NamespaceE\NamespaceF\ClassB as ClassC;
use ClassD;
use ClassE as ClassF;

EOD;

        $this->assertSame($expected, $this->renderer->renderContext($context));
    }

    public function testRenderContextWithGlobalNamespace()
    {
        $context = new ResolutionContext(
            null,
            array(
                new UseStatement(ClassName::fromString('\NamespaceC\NamespaceD\ClassA')),
                new UseStatement(
                    ClassName::fromString('\NamespaceE\NamespaceF\ClassB'),
                    ClassName::fromString('ClassC')
                ),
                new UseStatement(ClassName::fromString('\ClassD')),
                new UseStatement(ClassName::fromString('\ClassE'), ClassName::fromString('ClassF')),
            )
        );
        $expected = <<<'EOD'
use NamespaceC\NamespaceD\ClassA;
use NamespaceE\NamespaceF\ClassB as ClassC;
use ClassD;
use ClassE as ClassF;

EOD;

        $this->assertSame($expected, $this->renderer->renderContext($context));
    }

    public function testInstance()
    {
        $class = get_class($this->renderer);
        $liberatedClass = Liberator::liberateClass($class);
        $liberatedClass->instance = null;
        $actual = $class::instance();

        $this->assertInstanceOf($class, $actual);
        $this->assertSame($actual, $class::instance());
    }
}
