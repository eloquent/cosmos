<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Context;

use Eloquent\Cosmos\ClassName\ClassNameReferenceInterface;

/**
 * The interface implemented by class name resolution contexts.
 */
interface ResolutionContextInterface
{
    /**
     * Get the namespace.
     *
     * @return QualifiedClassNameInterface The namespace.
     */
    public function primaryNamespace();

    /**
     * Get the use statements.
     *
     * @return array<UseStatementInterface> The use statements.
     */
    public function useStatements();

    /**
     * Get the class name or namespace associated with the supplied short name.
     *
     * @param ClassNameReferenceInterface $shortName The short name.
     *
     * @return QualifiedClassNameInterface The class/namespace name.
     */
    public function classNameByShortName(
        ClassNameReferenceInterface $shortName
    );
}
