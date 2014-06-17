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

use Eloquent\Liberator\Liberator;
use PHPUnit_Framework_TestCase;

class TokenNormalizerTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->normalizer = new TokenNormalizer;
    }

    public function testNormalizeTokensEmpty()
    {
        $source = '';
        $actual = $this->normalizer->normalizeTokens(token_get_all($source));
        $expected = <<<'EOD'
end (1, 1): ''

EOD;

        $this->assertSame($expected, $this->renderTokenList($actual));
    }

    public function testNormalizeTokens()
    {
        $source = <<<'EOD'
<?php

    echo 'foo';

        echo 'bar';

    ;
EOD;
        $actual = $this->normalizer->normalizeTokens(token_get_all($source));
        $expected = <<<'EOD'
T_OPEN_TAG (1, 1): '<?php
'
T_WHITESPACE (2, 1): '
    '
T_ECHO (3, 5): 'echo'
T_WHITESPACE (3, 9): ' '
T_CONSTANT_ENCAPSED_STRING (3, 10): '\'foo\''
; (3, 15): ';'
T_WHITESPACE (3, 16): '

        '
T_ECHO (5, 9): 'echo'
T_WHITESPACE (5, 13): ' '
T_CONSTANT_ENCAPSED_STRING (5, 14): '\'bar\''
; (5, 19): ';'
T_WHITESPACE (5, 20): '

    '
; (7, 5): ';'
end (7, 6): ''

EOD;

        $this->assertSame($expected, $this->renderTokenList($actual));
    }

    public function testInstance()
    {
        $class = get_class($this->normalizer);
        $liberatedClass = Liberator::liberateClass($class);
        $liberatedClass->instance = null;
        $actual = $class::instance();

        $this->assertInstanceOf($class, $actual);
        $this->assertSame($actual, $class::instance());
    }

    protected function renderTokenList(array $tokens)
    {
        $rendered = '';
        foreach ($tokens as $token) {
            if (is_string($token[0])) {
                $tokenName = $token[0];
            } else {
                $tokenName = token_name($token[0]);
            }

            $rendered .= sprintf("%s (%d, %d): %s\n", $tokenName, $token[2], $token[3], var_export($token[1], true));
        }

        return $rendered;
    }
}
