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

use Eloquent\Cosmos\Resolution\Context\ResolutionContextInterface;
use Eloquent\Pathogen\AbsolutePathInterface;

/**
 * The interface implemented by fully qualified class names.
 */
interface QualifiedClassNameInterface extends
    AbsolutePathInterface,
    ClassNameInterface
{
    /**
     * Find the shortest class name that will resolve to this class name from
     * within the supplied resolution context.
     *
     * If this class is not a child of the primary namespace, and there are no
     * related use statements, this method will return a qualified class
     * name.
     *
     * @param ResolutionContextInterface $context The resolution context.
     *
     * @return ClassNameInterface The shortest class name.
     */
    public function relativeToContext(ResolutionContextInterface $context);
}
