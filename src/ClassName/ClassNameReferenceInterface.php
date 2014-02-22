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

use Eloquent\Pathogen\RelativePathInterface;

/**
 * The interface implemented by class name references.
 */
interface ClassNameReferenceInterface extends
    RelativePathInterface,
    ClassNameInterface
{
}