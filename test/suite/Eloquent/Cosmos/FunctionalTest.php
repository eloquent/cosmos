<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2012 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Eloquent\Cosmos\ClassNameResolver;

class FunctionalTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test example resolver.
     */
    public function testExampleResolver()
    {
        $resolver = new ClassNameResolver(
            'MilkyWay\SolarSystem',
            array(
                'MilkyWay\AlphaCentauri\ProximaCentauri' => null,
                'Andromeda\GalacticCenter' => 'Andromeda',
            )
        );

        $this->assertSame('MilkyWay\SolarSystem\Earth', $resolver->resolve('Earth'));
        $this->assertSame('MilkyWay\AlphaCentauri\ProximaCentauri', $resolver->resolve('ProximaCentauri'));
        $this->assertSame('Andromeda\GalacticCenter', $resolver->resolve('Andromeda'));
        $this->assertSame('MilkyWay\SolarSystem\TNO\Pluto', $resolver->resolve('TNO\Pluto'));
        $this->assertSame('Betelgeuse', $resolver->resolve('\Betelgeuse'));
    }
}
