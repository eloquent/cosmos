<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Cosmos\ClassName\Exception;

use Eloquent\Pathogen\Exception\AbstractInvalidPathAtomException;

/**
 * The supplied class name atom contains invalid characters.
 */
final class InvalidClassNameAtomException extends
    AbstractInvalidPathAtomException
{
    /**
     * Get the reason message.
     *
     * @return string The reason message.
     */
    public function reason()
    {
        return 'The atom contains invalid characters for a class name.';
    }
}
