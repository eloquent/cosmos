<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Exception;

use Exception;

/**
 * The supplied symbol atom contains invalid characters.
 */
final class InvalidSymbolAtomException extends Exception
{
    /**
     * Construct a new invalid symbol exception.
     *
     * @param string         $atom  The atom.
     * @param Exception|null $cause The cause, if available.
     */
    public function __construct($atom, Exception $cause = null)
    {
        $this->atom = $atom;

        parent::__construct(
            \sprintf('Invalid symbol atom %s.', \var_export($atom, true)),
            0,
            $cause
        );
    }

    /**
     * Get the atom.
     *
     * @return string The atom.
     */
    public function atom()
    {
        return $this->atom;
    }

    private $atom;
}
