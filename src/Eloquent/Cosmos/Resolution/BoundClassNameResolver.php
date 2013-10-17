<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution;

use Eloquent\Cosmos\ClassName\ClassNameInterface;
use Eloquent\Cosmos\ClassName\QualifiedClassNameInterface;

/**
 * Resolves class names against a bound context.
 */
class BoundClassNameResolver implements BoundClassNameResolverInterface
{
    /**
     * Construct a new bound class name resolver.
     *
     * @param ResolutionContextInterface|null $context  The resolution context.
     * @param ClassNameResolverInterface|null $resolver The internal class name resolver to use.
     */
    public function __construct(
        ResolutionContextInterface $context = null,
        ClassNameResolverInterface $resolver = null
    ) {
        if (null === $context) {
            $context = new ResolutionContext;
        }
        if (null === $resolver) {
            $resolver = new ClassNameResolver;
        }

        $this->context = $context;
        $this->resolver = $resolver;
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
     * Get the internal resolver.
     *
     * @return ClassNameResolverInterface The internal resolver.
     */
    public function resolver()
    {
        return $this->resolver;
    }

    /**
     * Resolve a class name.
     *
     * This method will resolve class name references against the bound context.
     * Class names that are already qualified will be returned unaltered.
     *
     * @param ClassNameInterface $className The class name to resolve.
     *
     * @return QualifiedClassNameInterface The resolved, qualified class name.
     */
    public function resolve(ClassNameInterface $className)
    {
        return $this->resolver()->resolve($this->context(), $className);
    }

    private $context;
    private $resolver;
}
