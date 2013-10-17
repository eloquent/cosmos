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
 * The interface implemented by class name resolvers.
 */
interface ClassNameResolverInterface
{
    /**
     * Resolve a class name.
     *
     * This method will resolve class name references against the supplied
     * context. Class names that are already qualified will be returned
     * unaltered.
     *
     * @param ResolutionContextInterface $context   The resolution context.
     * @param ClassNameInterface         $className The class name to resolve.
     *
     * @return QualifiedClassNameInterface The resolved, qualified class name.
     */
    public function resolve(
        ResolutionContextInterface $context,
        ClassNameInterface $className
    );
}
