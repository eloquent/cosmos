<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution;

use Eloquent\Cosmos\ClassName\QualifiedClassNameInterface;
use Eloquent\Cosmos\Resolution\Context\ResolutionContext;
use Eloquent\Cosmos\Resolution\Context\ResolutionContextInterface;
use Eloquent\Pathogen\PathInterface;
use Eloquent\Pathogen\Resolver\PathResolverInterface;

/**
 * Resolves class names against a fixed context.
 */
class FixedContextClassNameResolver implements PathResolverInterface
{
    /**
     * Construct a new fixed context class name resolver.
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
     * Resolve a class name against a fixed context.
     *
     * Class names that are already qualified will be returned unaltered.
     *
     * @param PathInterface $className The class name to resolve.
     *
     * @return QualifiedClassNameInterface The resolved, qualified class name.
     */
    public function resolve(PathInterface $className)
    {
        return $this->resolver()
            ->resolveAgainstContext($this->context(), $className);
    }

    private $context;
    private $resolver;
}
