<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Parser;

use Eloquent\Cosmos\ClassName\ClassName;
use Eloquent\Cosmos\Resolution\ResolutionContext;
use Eloquent\Liberator\Liberator;
use PHPUnit_Framework_TestCase;

class ResolutionContextParserTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->parser = new ResolutionContextParser;
    }

    public function testInstance()
    {
        $class = get_class($this->parser);
        $liberatedClass = Liberator::liberateClass($class);
        $liberatedClass->instance = null;
        $actual = $class::instance();

        $this->assertInstanceOf($class, $actual);
        $this->assertSame($actual, $class::instance());
    }

    public function testParseGlobalNamespaceNoUseStatements()
    {
        $source = <<<'EOD'
<?php
exit

;
EOD;
        $expected = array(new ParsedResolutionContext);

        $this->assertEquals($expected, $this->parser->parseSource($source));
    }

    public function testParseGlobalNamespaceSingleClass()
    {
        $source = <<<'EOD'
<?php
class ClassA {}
EOD;
        $contextA = new ResolutionContext;
        $classNameA = ClassName::fromString('\ClassA');
        $expected = array(new ParsedResolutionContext($contextA, array($classNameA)));

        $this->assertEquals($expected, $this->parser->parseSource($source));
    }

    public function testParseGlobalNamespaceMultipleTypes()
    {
        $source = <<<'EOD'
<?php
class ClassA {}
interface InterfaceA {}
interface InterfaceB {}
interface InterfaceC extends InterfaceA, InterfaceB {}
class ClassB extends ClassA implements InterfaceA, InterfaceB {}
EOD;
        $contextA = new ResolutionContext;
        $classNameA = ClassName::fromString('\ClassA');
        $classNameB = ClassName::fromString('\InterfaceA');
        $classNameC = ClassName::fromString('\InterfaceB');
        $classNameD = ClassName::fromString('\InterfaceC');
        $classNameE = ClassName::fromString('\ClassB');
        $classNames = array($classNameA, $classNameB, $classNameC, $classNameD, $classNameE);
        $expected = array(new ParsedResolutionContext($contextA, $classNames));

        $this->assertEquals($expected, $this->parser->parseSource($source));
    }

    public function testParseTraits()
    {
        if (!defined('T_TRAIT')) {
            $this->markTestSkipped('Requires trait support.');
        }

        $source = <<<'EOD'
<?php
trait TraitA {}
trait TraitB {}
EOD;
        $contextA = new ResolutionContext;
        $classNameA = ClassName::fromString('\TraitA');
        $classNameB = ClassName::fromString('\TraitB');
        $classNames = array($classNameA, $classNameB);
        $expected = array(new ParsedResolutionContext($contextA, $classNames));

        $this->assertEquals($expected, $this->parser->parseSource($source));
    }
}
