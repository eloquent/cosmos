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
 * @covers \Eloquent\Cosmos\Resolution\Context\ResolutionContextFactory
 */
class ResolutionContextFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->subject = new ResolutionContextFactory();

        $this->primaryNamespace = Symbol::fromString('\VendorA\PackageA');
        $this->useStatements = array(
            UseStatement::fromSymbol(Symbol::fromString('\VendorB\PackageB')),
            UseStatement::fromSymbol(Symbol::fromString('\VendorC\PackageC')),
        );
        $this->context = new ResolutionContext($this->primaryNamespace, $this->useStatements);
    }

    public function testCreateContext()
    {
        $actual = $this->subject->createContext($this->primaryNamespace, $this->useStatements);

        $this->assertEquals($this->context, $actual);
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
