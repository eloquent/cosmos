<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Eloquent\Cosmos\ClassName;
use Eloquent\Cosmos\ClassNameResolver;

class FunctionalTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test example resolver.
     */
    public function testExampleResolver()
    {
        $resolver = new ClassNameResolver(
            ClassName::fromString('\MilkyWay\SolarSystem'), // namespace
            array(
                array(
                    ClassName::fromString('\MilkyWay\AlphaCentauri\ProximaCentauri'), // use
                ),
                array(
                    ClassName::fromString('\Andromeda\GalacticCenter'), // use
                    ClassName::fromString('Andromeda'), // as
                ),
            )
        );

        $this->assertEquals(
            ClassName::fromString('\MilkyWay\SolarSystem\Earth'),
            strval($resolver->resolve(ClassName::fromString('Earth')))
        );
        $this->assertEquals(
            ClassName::fromString('\MilkyWay\AlphaCentauri\ProximaCentauri'),
            strval($resolver->resolve(ClassName::fromString('ProximaCentauri')))
        );
        $this->assertEquals(
            ClassName::fromString('\Andromeda\GalacticCenter'),
            strval($resolver->resolve(ClassName::fromString('Andromeda')))
        );
        $this->assertEquals(
            ClassName::fromString('\MilkyWay\SolarSystem\TNO\Pluto'),
            strval($resolver->resolve(ClassName::fromString('TNO\Pluto')))
        );
        $this->assertEquals(
            ClassName::fromString('\Betelgeuse'),
            strval($resolver->resolve(ClassName::fromString('\Betelgeuse')))
        );
    }
}
