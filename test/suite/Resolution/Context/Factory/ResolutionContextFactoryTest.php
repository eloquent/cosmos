<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Context\Factory;

use Eloquent\Cosmos\Resolution\Context\Parser\ResolutionContextParser;
use Eloquent\Cosmos\Resolution\Context\Renderer\ResolutionContextRenderer;
use Eloquent\Cosmos\Resolution\Context\ResolutionContext;
use Eloquent\Cosmos\Symbol\Factory\SymbolFactory;
use Eloquent\Cosmos\Symbol\Symbol;
use Eloquent\Cosmos\UseStatement\UseStatement;
use Eloquent\Liberator\Liberator;
use Phake;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class ResolutionContextFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->symbolFactory = new SymbolFactory;
        $this->contextParser = new ResolutionContextParser;
        $this->factory = new ResolutionContextFactory($this->symbolFactory, $this->contextParser);

        $this->primaryNamespace = $this->symbolFactory->create('\VendorA\PackageA');
        $this->useStatements = array(
            new UseStatement($this->symbolFactory->create('\VendorB\PackageB')),
            new UseStatement($this->symbolFactory->create('\VendorC\PackageC')),
        );
        $this->context = new ResolutionContext($this->primaryNamespace, $this->useStatements, $this->symbolFactory);
        $this->contextRenderer = ResolutionContextRenderer::instance();

        SymbolFactory::instance()->globalNamespace();
        $this->symbolFactory->globalNamespace();
    }

    public function testConstructor()
    {
        $this->assertSame($this->symbolFactory, $this->factory->symbolFactory());
        $this->assertSame($this->contextParser, $this->factory->contextParser());
    }

    public function testConstructorDefaults()
    {
        $this->factory = new ResolutionContextFactory;

        $this->assertSame(SymbolFactory::instance(), $this->factory->symbolFactory());
        $this->assertEquals(
            new ResolutionContextParser($this->symbolFactory, null, null, null, $this->factory),
            $this->factory->contextParser()
        );
    }

    public function testCreate()
    {
        $actual = $this->factory->create($this->primaryNamespace, $this->useStatements);

        $this->assertEquals($this->context, $actual);
    }

    public function testCreateFromObject()
    {
        $actual = $this->factory->createFromObject($this);
        $expected = <<<'EOD'
namespace Eloquent\Cosmos\Resolution\Context\Factory;

use Eloquent\Cosmos\Resolution\Context\Parser\ResolutionContextParser;
use Eloquent\Cosmos\Resolution\Context\Renderer\ResolutionContextRenderer;
use Eloquent\Cosmos\Resolution\Context\ResolutionContext;
use Eloquent\Cosmos\Symbol\Factory\SymbolFactory;
use Eloquent\Cosmos\Symbol\Symbol;
use Eloquent\Cosmos\UseStatement\UseStatement;
use Eloquent\Liberator\Liberator;
use Phake;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testCreateFromSymbol()
    {
        $actual = $this->factory->createFromSymbol(Symbol::fromRuntimeString(__CLASS__));
        $expected = <<<'EOD'
namespace Eloquent\Cosmos\Resolution\Context\Factory;

use Eloquent\Cosmos\Resolution\Context\Parser\ResolutionContextParser;
use Eloquent\Cosmos\Resolution\Context\Renderer\ResolutionContextRenderer;
use Eloquent\Cosmos\Resolution\Context\ResolutionContext;
use Eloquent\Cosmos\Symbol\Factory\SymbolFactory;
use Eloquent\Cosmos\Symbol\Symbol;
use Eloquent\Cosmos\UseStatement\UseStatement;
use Eloquent\Liberator\Liberator;
use Phake;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testCreateFromSymbolWithString()
    {
        $actual = $this->factory->createFromSymbol(__CLASS__);
        $expected = <<<'EOD'
namespace Eloquent\Cosmos\Resolution\Context\Factory;

use Eloquent\Cosmos\Resolution\Context\Parser\ResolutionContextParser;
use Eloquent\Cosmos\Resolution\Context\Renderer\ResolutionContextRenderer;
use Eloquent\Cosmos\Resolution\Context\ResolutionContext;
use Eloquent\Cosmos\Symbol\Factory\SymbolFactory;
use Eloquent\Cosmos\Symbol\Symbol;
use Eloquent\Cosmos\UseStatement\UseStatement;
use Eloquent\Liberator\Liberator;
use Phake;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testCreateFromSymbolFailureUndefined()
    {
        $this->setExpectedException('Eloquent\Cosmos\Exception\UndefinedSymbolException');
        $this->factory->createFromSymbol(Symbol::fromString('\Foo'));
    }

    public function testCreateFromClass()
    {
        $actual = $this->factory->createFromClass(new ReflectionClass(__CLASS__));
        $expected = <<<'EOD'
namespace Eloquent\Cosmos\Resolution\Context\Factory;

use Eloquent\Cosmos\Resolution\Context\Parser\ResolutionContextParser;
use Eloquent\Cosmos\Resolution\Context\Renderer\ResolutionContextRenderer;
use Eloquent\Cosmos\Resolution\Context\ResolutionContext;
use Eloquent\Cosmos\Symbol\Factory\SymbolFactory;
use Eloquent\Cosmos\Symbol\Symbol;
use Eloquent\Cosmos\UseStatement\UseStatement;
use Eloquent\Liberator\Liberator;
use Phake;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testCreateFromClassFailureFileSystemRead()
    {
        $class = Phake::mock('ReflectionClass');
        Phake::when($class)->getFileName()->thenReturn('/path/to/foo');

        $this->setExpectedException('Eloquent\Cosmos\Resolution\Context\Factory\Exception\SourceCodeReadException');
        $this->factory->createFromClass($class);
    }

    public function testCreateFromClassFailureNoMatchingSymbol()
    {
        $class = Phake::mock('ReflectionClass');
        Phake::when($class)->getName()->thenReturn('Foo');
        Phake::when($class)->getFileName()->thenReturn(__FILE__);

        $this->setExpectedException('Eloquent\Cosmos\Resolution\Context\Factory\Exception\SourceCodeReadException');
        $this->factory->createFromClass($class);
    }

    public function testInstance()
    {
        $class = get_class($this->factory);
        $liberatedClass = Liberator::liberateClass($class);
        $liberatedClass->instance = null;
        $actual = $class::instance();

        $this->assertInstanceOf($class, $actual);
        $this->assertSame($actual, $class::instance());
    }
}
