<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Parser;

use Eloquent\Cosmos\Resolution\ResolutionContextInterface;

/**
 * The interface implemented by parsed resolution contexts.
 */
interface ParsedResolutionContextInterface
{
    /**
     * Get the resolution context.
     *
     * @return ResolutionContextInterface The resolution context.
     */
    public function context();

    /**
     * Get the list of class names defined under the parsed resolution context.
     *
     * @return array<QualifiedClassNameInterface> The defined class names.
     */
    public function classNames();
}
