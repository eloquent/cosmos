<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Context;

use Eloquent\Cosmos\Symbol\Symbol;
use Eloquent\Cosmos\UseStatement\UseStatement;
use Eloquent\Liberator\Liberator;
use PHPUnit_Framework_TestCase;

/**
 * @covers \Eloquent\Cosmos\Resolution\Context\ResolutionContextRenderer
 */
class ResolutionContextRendererTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->subject = new ResolutionContextRenderer();
    }

    public function testRenderContext()
    {
        $primaryNamespace = Symbol::fromString('\VendorA\PackageA');
        $useStatements = array(
            UseStatement::fromSymbol(Symbol::fromString('\NamespaceA\NamespaceB\SymbolA'), 'SymbolB'),
            UseStatement::fromSymbol(Symbol::fromString('\NamespaceC\NamespaceD')),
            UseStatement::fromSymbol(Symbol::fromString('\SymbolC')),
        );
        $context = new ResolutionContext($primaryNamespace, $useStatements);
        $expected = <<<'EOD'
namespace VendorA\PackageA;

use NamespaceA\NamespaceB\SymbolA as SymbolB;
use NamespaceC\NamespaceD;
use SymbolC;

EOD;

        $this->assertSame($expected, $this->subject->renderContext($context));
    }

    public function testInstance()
    {
        $class = get_class($this->subject);
        $liberatedClass = Liberator::liberateClass($class);
        $liberatedClass->instance = null;
        $actual = $class::instance();

        $this->assertInstanceOf($class, $actual);
        $this->assertSame($actual, $class::instance());
    }
}
