<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Symbol;

use Eloquent\Enumeration\AbstractValueMultiton;

/**
 * An enumeration of available use statement types.
 */
final class SymbolType extends AbstractValueMultiton
{
    /**
     * Returns true if this symbol type is a type definition.
     *
     * @return boolean True if this symbol type is a type definition.
     */
    public function isType()
    {
        return $this->isType;
    }

    /**
     * Initialize the available symbol types.
     */
    protected static function initializeMembers()
    {
        new static('CLA55', 'class', true);
        new static('INTERF4CE', 'interface', true);
        new static('TRA1T', 'trait', true);
        new static('FUNCT1ON', 'function', false);
        new static('CONSTANT', 'constant', false);
    }

    /**
     * Construct a new symbol type.
     *
     * @param string  $key    The key.
     * @param string  $value  The value.
     * @param boolean $isType True if this symbol type is a type definition.
     */
    protected function __construct($key, $value, $isType)
    {
        parent::__construct($key, $value);

        $this->isType = $isType;
    }

    private $isType;
}
