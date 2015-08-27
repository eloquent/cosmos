<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Symbol\Exception;

use Eloquent\Pathogen\Exception\AbstractInvalidPathAtomException;

/**
 * The supplied symbol atom contains invalid characters.
 */
final class InvalidSymbolAtomException extends AbstractInvalidPathAtomException
{
    /**
     * Get the reason message.
     *
     * @return string The reason message.
     */
    public function reason()
    {
        return 'The atom contains invalid characters for a symbol.';
    }
}
