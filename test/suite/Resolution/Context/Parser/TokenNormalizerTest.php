<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Context\Parser;

use Eloquent\Liberator\Liberator;
use PHPUnit_Framework_TestCase;

/**
 * @covers \Eloquent\Cosmos\Resolution\Context\Parser\TokenNormalizer
 */
class TokenNormalizerTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->subject = new TokenNormalizer();
    }

    public function testNormalizeTokens()
    {
        $actual = $this->subject->normalizeTokens(token_get_all('<?php "' . "\n\n" . '";'));
        $expected = array(
            array(T_OPEN_TAG,                 '<?php ',   1, 1, 0,  5),
            array(T_CONSTANT_ENCAPSED_STRING, "\"\n\n\"", 1, 7, 6,  9),
            array(';',                        ';',        3, 2, 10, 10),
            array('end',                      '',         3, 3, 11, 11),
        );

        $this->assertSame($expected, $actual);
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
