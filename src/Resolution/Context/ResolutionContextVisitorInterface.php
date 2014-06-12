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
use Eloquent\Cosmos\ClassName\QualifiedClassNameInterface;
use Eloquent\Cosmos\UseStatement\UseStatementInterface;

/**
 * The interface implemented by class name resolution context visitors.
 */
interface ResolutionContextVisitorInterface
{
    /**
     * Visit a resolution context.
     *
     * @param ResolutionContextInterface $context The context to visit.
     *
     * @return mixed The result of visitation.
     */
    public function visitResolutionContext(ResolutionContextInterface $context);

    /**
     * Visit a use statement.
     *
     * @param UseStatementInterface $useStatement The use statement to visit.
     *
     * @return mixed The result of visitation.
     */
    public function visitUseStatement(UseStatementInterface $useStatement);

    /**
     * Visit a qualified class name.
     *
     * @param QualifiedClassNameInterface $className The class name to visit.
     *
     * @return mixed The result of visitation.
     */
    public function visitQualifiedClassName(
        QualifiedClassNameInterface $className
    );

    /**
     * Visit a class name reference.
     *
     * @param ClassNameReferenceInterface $className The class name to visit.
     *
     * @return mixed The result of visitation.
     */
    public function visitClassNameReference(
        ClassNameReferenceInterface $className
    );
}
