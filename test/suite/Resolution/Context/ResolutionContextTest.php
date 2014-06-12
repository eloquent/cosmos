<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Context;

use Eloquent\Cosmos\ClassName\ClassName;
use Eloquent\Cosmos\ClassName\Factory\ClassNameFactory;
use Eloquent\Cosmos\ClassName\QualifiedClassName;
use Eloquent\Cosmos\UseStatement\UseStatement;
use Phake;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class ResolutionContextTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->factory = new ClassNameFactory;
        $this->primaryNamespace = $this->factory->create('\VendorA\PackageA');
        $this->useStatements = array(
            new UseStatement($this->factory->create('\VendorB\PackageB')),
            new UseStatement($this->factory->create('\VendorC\PackageC')),
        );
        $this->context = new ResolutionContext($this->primaryNamespace, $this->useStatements, $this->factory);
    }

    public function testConstructor()
    {
        $this->assertSame($this->primaryNamespace, $this->context->primaryNamespace());
        $this->assertSame($this->useStatements, $this->context->useStatements());
    }

    public function testConstructorDefaults()
    {
        $this->context = new ResolutionContext;

        $this->assertEquals(new QualifiedClassName(array()), $this->context->primaryNamespace());
        $this->assertSame(array(), $this->context->useStatements());
    }

    public function testClassNameByShortName()
    {
        $this->context = new ResolutionContext(
            $this->factory->create('\foo'),
            array(
                new UseStatement($this->factory->create('\My\Full\Classname'), $this->factory->create('Another')),
                new UseStatement($this->factory->create('\My\Full\NSname')),
                new UseStatement($this->factory->create('\ArrayObject')),
            )
        );

        $this->assertSame(
            '\My\Full\Classname',
            $this->context->classNameByShortName($this->factory->create('Another'))->string()
        );
        $this->assertSame(
            '\My\Full\NSname',
            $this->context->classNameByShortName($this->factory->create('NSname'))->string()
        );
        $this->assertSame(
            '\ArrayObject',
            $this->context->classNameByShortName($this->factory->create('ArrayObject'))->string()
        );
        $this->assertNull($this->context->classNameByShortName($this->factory->create('Classname')));
        $this->assertNull($this->context->classNameByShortName($this->factory->create('FooClass')));
    }

    public function testFromObject()
    {
        $actual = ResolutionContext::fromObject($this);
        $expected = <<<'EOD'
namespace Eloquent\Cosmos\Resolution\Context;

use Eloquent\Cosmos\ClassName\ClassName;
use Eloquent\Cosmos\ClassName\Factory\ClassNameFactory;
use Eloquent\Cosmos\ClassName\QualifiedClassName;
use Eloquent\Cosmos\UseStatement\UseStatement;
use Phake;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

EOD;

        $this->assertSame($expected, $this->renderContext($actual));
    }

    public function testFromClass()
    {
        $actual = ResolutionContext::fromClass(ClassName::fromRuntimeString(__CLASS__));
        $expected = <<<'EOD'
namespace Eloquent\Cosmos\Resolution\Context;

use Eloquent\Cosmos\ClassName\ClassName;
use Eloquent\Cosmos\ClassName\Factory\ClassNameFactory;
use Eloquent\Cosmos\ClassName\QualifiedClassName;
use Eloquent\Cosmos\UseStatement\UseStatement;
use Phake;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

EOD;

        $this->assertSame($expected, $this->renderContext($actual));
    }

    public function testFromReflector()
    {
        $actual = ResolutionContext::fromReflector(new ReflectionClass(__CLASS__));
        $expected = <<<'EOD'
namespace Eloquent\Cosmos\Resolution\Context;

use Eloquent\Cosmos\ClassName\ClassName;
use Eloquent\Cosmos\ClassName\Factory\ClassNameFactory;
use Eloquent\Cosmos\ClassName\QualifiedClassName;
use Eloquent\Cosmos\UseStatement\UseStatement;
use Phake;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

EOD;

        $this->assertSame($expected, $this->renderContext($actual));
    }

    public function testAccept()
    {
        $visitor = Phake::mock('Eloquent\Cosmos\Resolution\Context\ResolutionContextVisitorInterface');
        $this->context->accept($visitor);

        Phake::verify($visitor)->visitResolutionContext($this->identicalTo($this->context));
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
