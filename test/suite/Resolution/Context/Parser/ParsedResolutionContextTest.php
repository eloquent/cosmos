<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Context\Parser;

use Eloquent\Cosmos\ClassName\ClassName;
use Eloquent\Cosmos\Resolution\Context\ResolutionContext;
use PHPUnit_Framework_TestCase;

class ParsedResolutionContextTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->context = new ResolutionContext;
        $this->classNames = array(ClassName::fromString('\ClassA'), ClassName::fromString('\ClassB'));
        $this->parsedContext = new ParsedResolutionContext($this->context, $this->classNames);
    }

    public function testConstructor()
    {
        $this->assertSame($this->context, $this->parsedContext->context());
        $this->assertSame($this->classNames, $this->parsedContext->classNames());
    }

    public function testConstructorDefaults()
    {
        $this->parsedContext = new ParsedResolutionContext;

        $this->assertEquals(new ResolutionContext, $this->parsedContext->context());
        $this->assertSame(array(), $this->parsedContext->classNames());
    }
}
