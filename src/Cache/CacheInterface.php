<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Cache;

/**
 * The interface implemented by caches.
 *
 * @api
 */
interface CacheInterface
{
    /**
     * Get a value from the cache.
     *
     * @param string $key The key.
     *
     * @return mixed The value.
     */
    public function get($key);

    /**
     * Set a value to the cache.
     *
     * @param string $key   The key.
     * @param mixed  $value The value.
     *
     * @return mixed The value.
     */
    public function set($key, $value);
}
