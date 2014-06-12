<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Context\Renderer;

use Eloquent\Cosmos\ClassName\ClassNameReferenceInterface;
use Eloquent\Cosmos\ClassName\QualifiedClassNameInterface;
use Eloquent\Cosmos\Resolution\Context\ResolutionContextInterface;
use Eloquent\Cosmos\Resolution\Context\ResolutionContextVisitorInterface;
use Eloquent\Cosmos\UseStatement\UseStatementInterface;

/**
 * Renders resolution contexts using standard PHP syntax.
 */
class ResolutionContextRenderer implements ResolutionContextRendererInterface,
    ResolutionContextVisitorInterface
{
    /**
     * Get a static instance of this renderer.
     *
     * @return ResolutionContextRendererInterface The static renderer.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Render a class name resolution context.
     *
     * @param ResolutionContextInterface $context The context to render.
     *
     * @return string The rendered context.
     */
    public function renderContext(ResolutionContextInterface $context)
    {
        return $context->accept($this);
    }

    /**
     * Visit a resolution context.
     *
     * @param ResolutionContextInterface $context The context to visit.
     *
     * @return mixed The result of visitation.
     */
    public function visitResolutionContext(ResolutionContextInterface $context)
    {
        if (!$context->primaryNamespace()->isRoot()) {
            $rendered = 'namespace ' .
                $context->primaryNamespace()->accept($this) . ";\n";
        } else {
            $rendered = '';
        }

        if ('' !== $rendered && count($context->useStatements()) > 0) {
            $rendered .= "\n";
        }

        foreach ($context->useStatements() as $useStatement) {
            $rendered .= $useStatement->accept($this) . ";\n";
        }

        return $rendered;
    }

    /**
     * Visit a use statement.
     *
     * @param UseStatementInterface $useStatement The use statement to visit.
     *
     * @return mixed The result of visitation.
     */
    public function visitUseStatement(UseStatementInterface $useStatement)
    {
        $rendered = 'use ' . $useStatement->className()->accept($this);
        if (null !== $useStatement->alias()) {
            $rendered .= ' as ' . $useStatement->alias()->accept($this);
        }

        return $rendered;
    }

    /**
     * Visit a qualified class name.
     *
     * @param QualifiedClassNameInterface $className The class name to visit.
     *
     * @return mixed The result of visitation.
     */
    public function visitQualifiedClassName(
        QualifiedClassNameInterface $className
    ) {
        return $className->toRelative()->accept($this);
    }

    /**
     * Visit a class name reference.
     *
     * @param ClassNameReferenceInterface $className The class name to visit.
     *
     * @return mixed The result of visitation.
     */
    public function visitClassNameReference(
        ClassNameReferenceInterface $className
    ) {
        return $className->string();
    }

    private static $instance;
}
