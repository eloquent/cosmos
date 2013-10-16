<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Cosmos\ClassName;

use PHPUnit_Framework_TestCase;

class ResolutionContextTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->factory = new Factory\ClassNameFactory;
        $this->primaryNamespace = $this->factory->create('\\VendorA\\PackageA');
        $this->useStatements = array(
            new UseStatement($this->factory->create('\\VendorB\\PackageB')),
            new UseStatement($this->factory->create('\\VendorC\\PackageC')),
        );
        $this->context = new ResolutionContext($this->primaryNamespace, $this->useStatements);
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
}
