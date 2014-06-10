<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Parser\Exception;

use Exception;

/**
 * An unexpected token was encountered.
 */
final class UnexpectedTokenException extends Exception
{
    /**
     * Construct a new unexpected token exception.
     *
     * @param array<integer,mixed> $token The unexpected token.
     * @param string               $state The state in which the token was encountered.
     * @param Exception|null       $cause The cause, if available.
     */
    public function __construct(array $token, $state, Exception $cause = null)
    {
        $this->token = $token;
        $this->state = $state;

        parent::__construct(
            sprintf(
                'An unexpected token of type %s was encountered in state %s.',
                $token[0],
                $state
            ),
            0,
            $cause
        );
    }

    /**
     * Get the unexpected token.
     *
     * @return array<integer,mixed> The token.
     */
    public function token()
    {
        return $this->token;
    }

    /**
     * Get the state in which the token was encountered.
     *
     * @return string The state.
     */
    public function state()
    {
        return $this->state;
    }

    private $token;
    private $state;
}
