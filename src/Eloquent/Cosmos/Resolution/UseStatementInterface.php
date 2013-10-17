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

/**
 * The interface implemented by use statements.
 */
interface UseStatementInterface
{
    /**
     * Get the class name.
     *
     * @return QualifiedClassNameInterface The class name.
     */
    public function className();

    /**
     * Get the alias for the class name.
     *
     * @return ClassNameReferenceInterface|null The alias, or null if no alias is in use.
     */
    public function alias();
}
