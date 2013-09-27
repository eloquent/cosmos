<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Statement;

use Eloquent\Cosmos\ClassName\QualifiedClassNameInterface;

/**
 * Represents a use statement.
 */
class UseStatement implements UseStatementInterface
{
    /**
     * Construct a new use statement.
     *
     * @param QualifiedClassNameInterface $className The class name.
     * @param string|null                 $alias     The alias for the class name.
     */
    public function __construct(
        QualifiedClassNameInterface $className,
        $alias = null
    ) {
        $this->className = $className;
        $this->alias = $alias;
    }

    /**
     * Get the class name.
     *
     * @return QualifiedClassNameInterface The class name.
     */
    public function className()
    {
        return $this->className;
    }

    /**
     * Get the alias for the class name.
     *
     * @return string|null The alias, or null if no alias is in use.
     */
    public function alias()
    {
        return $this->alias;
    }

    private $className;
    private $alias;
}
