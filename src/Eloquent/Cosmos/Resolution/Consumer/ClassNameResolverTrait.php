<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Consumer;

use Eloquent\Cosmos\Resolution\ClassNameResolver;
use Eloquent\Cosmos\Resolution\ClassNameResolverInterface;

/**
 * A trait for classes that take a class name resolver as a dependency.
 */
trait ClassNameResolverTrait
{
    /**
     * Set the class name resolver.
     *
     * @param ClassNameResolverInterface $classNameResolver
     */
    public function setClassNameResolver(
        ClassNameResolverInterface $classNameResolver
    ) {
        $this->classNameResolver = $classNameResolver;
    }

    /**
     * Get the class name resolver.
     *
     * @return ClassNameResolverInterface The class name resolver.
     */
    public function classNameResolver()
    {
        if (null === $this->classNameResolver) {
            $this->classNameResolver = $this->createDefaultClassNameResolver();
        }

        return $this->classNameResolver;
    }

    /**
     * Create a default class name resolver.
     *
     * @return ClassNameResolverInterface The new class name resolver.
     */
    protected function createDefaultClassNameResolver()
    {
        return new ClassNameResolver;
    }

    private $classNameResolver;
}
