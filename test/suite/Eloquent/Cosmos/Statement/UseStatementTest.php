<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Statement;

use Eloquent\Cosmos\ClassName\Factory\ClassNameFactory;
use PHPUnit_Framework_TestCase;

class UseStatementTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->factory = new ClassNameFactory;
        $this->className = $this->factory->create('\\Namespace\\Class');
        $this->useStatement = new UseStatement($this->className, 'foo');
    }

    public function testConstructor()
    {
        $this->assertSame($this->className, $this->useStatement->className());
        $this->assertSame('foo', $this->useStatement->alias());
    }

    public function testConstructorDefaults()
    {
        $this->useStatement = new UseStatement($this->className);

        $this->assertNull($this->useStatement->alias());
    }
}
