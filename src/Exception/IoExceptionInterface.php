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

/**
 * The interface implemented by I/O exceptions.
 */
interface IoExceptionInterface
{
    /**
     * Get the path.
     *
     * @return string|null The path, if known.
     */
    public function path();
}
