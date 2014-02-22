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

use Eloquent\Pathogen\AbsolutePathInterface;

/**
 * The interface implemented by fully qualified class names.
 */
interface QualifiedClassNameInterface extends
    AbsolutePathInterface,
    ClassNameInterface
{
}
