<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Cosmos\ClassName;

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
     * Resolve a class name reference against this context.
     *
     * @param ClassNameReferenceInterface $reference The reference to resolve.
     *
     * @return QualifiedClassNameInterface The resolved, qualified class name.
     */
    public function resolve(ClassNameReferenceInterface $reference);
}
