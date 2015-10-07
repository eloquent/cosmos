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
 * A cache that stores values in memory.
 *
 * @api
 */
class MemoryCache implements CacheInterface
{
    /**
     * Get a value from the cache.
     *
     * @param string $key The key.
     *
     * @return mixed The value.
     */
    public function get($key)
    {
        if (isset($this->values[$key])) {
            return $this->values[$key];
        }

        return null;
    }

    /**
     * Set a value to the cache.
     *
     * @param string $key   The key.
     * @param mixed  $value The value.
     *
     * @return mixed The value.
     */
    public function set($key, $value)
    {
        $this->values[$key] = $value;
    }

    private $values = array();
}
