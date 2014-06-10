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

use Eloquent\Cosmos\Resolution\Context\ResolutionContext;
use Eloquent\Cosmos\Resolution\Context\ResolutionContextInterface;

/**
 * Represents a parsed resolution context and its related class names.
 */
class ParsedResolutionContext implements ParsedResolutionContextInterface
{
    /**
     * Construct a new parsed resolution context.
     *
     * @param ResolutionContextInterface|null         $context    The resolution context.
     * @param array<QualifiedClassNameInterface>|null $classNames The class names defined under the parsed resolution context.
     */
    public function __construct(
        ResolutionContextInterface $context = null,
        array $classNames = null
    ) {
        if (null === $context) {
            $context = new ResolutionContext;
        }
        if (null === $classNames) {
            $classNames = array();
        }

        $this->context = $context;
        $this->classNames = $classNames;
    }

    /**
     * Get the resolution context.
     *
     * @return ResolutionContextInterface The resolution context.
     */
    public function context()
    {
        return $this->context;
    }

    /**
     * Get the class names defined under the parsed resolution context.
     *
     * @return array<QualifiedClassNameInterface> The defined class names.
     */
    public function classNames()
    {
        return $this->classNames;
    }

    private $context;
    private $classNames;
}
