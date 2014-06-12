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

use Eloquent\Cosmos\ClassName\ClassName;
use Eloquent\Cosmos\ClassName\Factory\ClassNameFactory;
use Eloquent\Cosmos\Resolution\Context\ResolutionContext;
use Eloquent\Cosmos\Resolution\Context\ResolutionContextInterface;
use Eloquent\Cosmos\Resolution\Parser\ResolutionContextParser;
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

        $this->classNameFactory = new ClassNameFactory;
        $this->contextParser = new ResolutionContextParser;
        $this->factory = new ResolutionContextFactory($this->classNameFactory, $this->contextParser);

        $this->primaryNamespace = $this->classNameFactory->create('\VendorA\PackageA');
        $this->useStatements = array(
            new UseStatement($this->classNameFactory->create('\VendorB\PackageB')),
            new UseStatement($this->classNameFactory->create('\VendorC\PackageC')),
        );
        $this->context = new ResolutionContext($this->primaryNamespace, $this->useStatements, $this->classNameFactory);

        $this->classNameFactory->globalNamespace();
    }

    public function testConstructor()
    {
        $this->assertSame($this->classNameFactory, $this->factory->classNameFactory());
        $this->assertSame($this->contextParser, $this->factory->contextParser());
    }

    public function testConstructorDefaults()
    {
        $this->factory = new ResolutionContextFactory;

        $this->assertSame(ClassNameFactory::instance(), $this->factory->classNameFactory());
        $this->assertEquals(
            new ResolutionContextParser($this->classNameFactory, null, null, null, $this->factory),
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

use Eloquent\Cosmos\ClassName\ClassName;
use Eloquent\Cosmos\ClassName\Factory\ClassNameFactory;
use Eloquent\Cosmos\Resolution\Context\ResolutionContext;
use Eloquent\Cosmos\Resolution\Context\ResolutionContextInterface;
use Eloquent\Cosmos\Resolution\Parser\ResolutionContextParser;
use Eloquent\Cosmos\UseStatement\UseStatement;
use Eloquent\Liberator\Liberator;
use Phake;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

EOD;

        $this->assertSame($expected, $this->renderContext($actual));
    }

    public function testCreateFromClass()
    {
        $actual = $this->factory->createFromClass(ClassName::fromRuntimeString(__CLASS__));
        $expected = <<<'EOD'
namespace Eloquent\Cosmos\Resolution\Context\Factory;

use Eloquent\Cosmos\ClassName\ClassName;
use Eloquent\Cosmos\ClassName\Factory\ClassNameFactory;
use Eloquent\Cosmos\Resolution\Context\ResolutionContext;
use Eloquent\Cosmos\Resolution\Context\ResolutionContextInterface;
use Eloquent\Cosmos\Resolution\Parser\ResolutionContextParser;
use Eloquent\Cosmos\UseStatement\UseStatement;
use Eloquent\Liberator\Liberator;
use Phake;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

EOD;

        $this->assertSame($expected, $this->renderContext($actual));
    }

    public function testCreateFromClassWithString()
    {
        $actual = $this->factory->createFromClass(__CLASS__);
        $expected = <<<'EOD'
namespace Eloquent\Cosmos\Resolution\Context\Factory;

use Eloquent\Cosmos\ClassName\ClassName;
use Eloquent\Cosmos\ClassName\Factory\ClassNameFactory;
use Eloquent\Cosmos\Resolution\Context\ResolutionContext;
use Eloquent\Cosmos\Resolution\Context\ResolutionContextInterface;
use Eloquent\Cosmos\Resolution\Parser\ResolutionContextParser;
use Eloquent\Cosmos\UseStatement\UseStatement;
use Eloquent\Liberator\Liberator;
use Phake;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

EOD;

        $this->assertSame($expected, $this->renderContext($actual));
    }

    public function testCreateFromClassFailureUndefined()
    {
        $this->setExpectedException('Eloquent\Cosmos\Exception\UndefinedClassException');
        $this->factory->createFromClass(ClassName::fromString('\Foo'));
    }

    public function testCreateFromReflector()
    {
        $actual = $this->factory->createFromReflector(new ReflectionClass(__CLASS__));
        $expected = <<<'EOD'
namespace Eloquent\Cosmos\Resolution\Context\Factory;

use Eloquent\Cosmos\ClassName\ClassName;
use Eloquent\Cosmos\ClassName\Factory\ClassNameFactory;
use Eloquent\Cosmos\Resolution\Context\ResolutionContext;
use Eloquent\Cosmos\Resolution\Context\ResolutionContextInterface;
use Eloquent\Cosmos\Resolution\Parser\ResolutionContextParser;
use Eloquent\Cosmos\UseStatement\UseStatement;
use Eloquent\Liberator\Liberator;
use Phake;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

EOD;

        $this->assertSame($expected, $this->renderContext($actual));
    }

    public function testCreateFromReflectorFailureFileSystemRead()
    {
        $reflector = Phake::mock('ReflectionClass');
        Phake::when($reflector)->getFileName()->thenReturn('/path/to/foo');

        $this->setExpectedException('Eloquent\Cosmos\Resolution\Context\Factory\Exception\SourceCodeReadException');
        $this->factory->createFromReflector($reflector);
    }

    public function testCreateFromReflectorFailureNoMatchingClassName()
    {
        $reflector = Phake::mock('ReflectionClass');
        Phake::when($reflector)->getName()->thenReturn('Foo');
        Phake::when($reflector)->getFileName()->thenReturn(__FILE__);

        $this->setExpectedException('Eloquent\Cosmos\Resolution\Context\Factory\Exception\SourceCodeReadException');
        $this->factory->createFromReflector($reflector);
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

    protected function renderContext(ResolutionContextInterface $context)
    {
        $rendered = '';
        if ($context->primaryNamespace()->isRoot()) {
            $rendered .= "namespace;\n\n";
        } else {
            $rendered .= sprintf("namespace %s;\n\n", $context->primaryNamespace()->toRelative()->string());
        }

        foreach ($context->useStatements() as $useStatement) {
            $rendered .= $useStatement->string() . ";\n";
        }

        return $rendered;
    }
}
