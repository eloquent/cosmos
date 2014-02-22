<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\ClassName;

use Eloquent\Pathogen\PathInterface;

/**
 * The common interface implemented by class names and class name references.
 */
interface ClassNameInterface extends PathInterface
{
    /**
     * Get the last atom of this class name as a class name reference.
     *
     * If this class name is already a short class name reference, it will be
     * returned unaltered.
     *
     * @return ClassNameReferenceInterface The short class name.
     */
    public function shortName();
}
