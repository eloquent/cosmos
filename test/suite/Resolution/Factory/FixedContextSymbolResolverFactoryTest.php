<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Factory;

use Eloquent\Cosmos\Resolution\Context\Parser\ParserPosition;
use Eloquent\Cosmos\Resolution\Context\Persistence\ResolutionContextReader;
use Eloquent\Cosmos\Resolution\Context\ResolutionContext;
use Eloquent\Cosmos\Resolution\FixedContextSymbolResolver;
use Eloquent\Cosmos\Resolution\SymbolResolver;
use Eloquent\Cosmos\Symbol\Symbol;
use Eloquent\Liberator\Liberator;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionFunction;

class FixedContextSymbolResolverFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->resolver = new SymbolResolver;
        $this->contextReader = new ResolutionContextReader;
        $this->factory = new FixedContextSymbolResolverFactory($this->resolver, $this->contextReader);

        $this->stream = fopen(__FILE__, 'rb');
    }

    protected function tearDown()
    {
        parent::tearDown();

        fclose($this->stream);
    }

    public function testConstructor()
    {
        $this->assertSame($this->resolver, $this->factory->resolver());
        $this->assertSame($this->contextReader, $this->factory->contextReader());
    }

    public function testConstructorDefaults()
    {
        $this->factory = new FixedContextSymbolResolverFactory;

        $this->assertSame(SymbolResolver::instance(), $this->factory->resolver());
        $this->assertSame(ResolutionContextReader::instance(), $this->factory->contextReader());
    }

    public function testCreate()
    {
        $context = new ResolutionContext;
        $actual = $this->factory->create($context);
        $expected = new FixedContextSymbolResolver($context, $this->resolver);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateFromObject()
    {
        $actual = $this->factory->createFromObject($this);
        $expected = new FixedContextSymbolResolver($this->contextReader->readFromObject($this), $this->resolver);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateFromSymbol()
    {
        $symbol = Symbol::fromRuntimeString(__CLASS__);
        $actual = $this->factory->createFromSymbol($symbol);
        $expected = new FixedContextSymbolResolver($this->contextReader->readFromSymbol($symbol), $this->resolver);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateFromFunctionSymbol()
    {
        $symbol = Symbol::fromRuntimeString('\printf');
        $actual = $this->factory->createFromFunctionSymbol($symbol);
        $expected =
            new FixedContextSymbolResolver($this->contextReader->readFromFunctionSymbol($symbol), $this->resolver);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateFromClass()
    {
        $class = new ReflectionClass(__CLASS__);
        $actual = $this->factory->createFromClass($class);
        $expected = new FixedContextSymbolResolver($this->contextReader->readFromClass($class), $this->resolver);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateFromFunction()
    {
        $function = new ReflectionFunction('\printf');
        $actual = $this->factory->createFromFunction($function);
        $expected = new FixedContextSymbolResolver($this->contextReader->readFromFunction($function), $this->resolver);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateFromFile()
    {
        $actual = $this->factory->createFromFile(__FILE__);
        $expected = new FixedContextSymbolResolver($this->contextReader->readFromFile(__FILE__), $this->resolver);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateFromFileByIndex()
    {
        $actual = $this->factory->createFromFileByIndex(__FILE__, 0);
        $expected =
            new FixedContextSymbolResolver($this->contextReader->readFromFileByIndex(__FILE__, 0), $this->resolver);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateFromFileByPosition()
    {
        $actual = $this->factory->createFromFileByPosition(__FILE__, new ParserPosition(111, 222));
        $expected = new FixedContextSymbolResolver(
            $this->contextReader->readFromFileByPosition(__FILE__, new ParserPosition(111, 222)),
            $this->resolver
        );

        $this->assertEquals($expected, $actual);
    }

    public function testCreateFromStream()
    {
        $actual = $this->factory->createFromStream($this->stream, __FILE__);
        $expected = new FixedContextSymbolResolver($this->contextReader->readFromFile(__FILE__), $this->resolver);

        $this->assertSame($this->resolver, $actual->resolver());
        $this->assertEquals($expected, $actual);
    }

    public function testCreateFromStreamByIndex()
    {
        $actual = $this->factory->createFromStreamByIndex($this->stream, 0, __FILE__);
        $expected =
            new FixedContextSymbolResolver($this->contextReader->readFromFileByIndex(__FILE__, 0), $this->resolver);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateFromStreamByPosition()
    {
        $actual = $this->factory->createFromStreamByPosition($this->stream, new ParserPosition(111, 222), __FILE__);
        $expected = new FixedContextSymbolResolver(
            $this->contextReader->readFromFileByPosition(__FILE__, new ParserPosition(111, 222)),
            $this->resolver
        );

        $this->assertEquals($expected, $actual);
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
