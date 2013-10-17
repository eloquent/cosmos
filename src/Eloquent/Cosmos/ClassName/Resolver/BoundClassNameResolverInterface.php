<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Cosmos\ClassName\Resolver;

use Eloquent\Cosmos\ClassName\ClassNameInterface;
use Eloquent\Cosmos\ClassName\QualifiedClassNameInterface;

/**
 * The interface implemented by class name resolvers that use a bound context.
 */
interface BoundClassNameResolverInterface
{
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
    public function resolve(ClassNameInterface $className);
}
