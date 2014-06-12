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

use Eloquent\Cosmos\Resolution\Context\ResolutionContextInterface;
use Eloquent\Cosmos\Resolution\Context\ResolutionContextVisitorInterface;
use Eloquent\Cosmos\Symbol\QualifiedSymbolInterface;
use Eloquent\Cosmos\Symbol\SymbolReferenceInterface;
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
     * Render a symbol resolution context.
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
        $rendered = 'use ' . $useStatement->symbol()->accept($this);
        if (null !== $useStatement->alias()) {
            $rendered .= ' as ' . $useStatement->alias()->accept($this);
        }

        return $rendered;
    }

    /**
     * Visit a qualified symbol.
     *
     * @param QualifiedSymbolInterface $symbol The symbol to visit.
     *
     * @return mixed The result of visitation.
     */
    public function visitQualifiedSymbol(QualifiedSymbolInterface $symbol)
    {
        return $symbol->toRelative()->accept($this);
    }

    /**
     * Visit a symbol reference.
     *
     * @param SymbolReferenceInterface $symbol The symbol to visit.
     *
     * @return mixed The result of visitation.
     */
    public function visitSymbolReference(SymbolReferenceInterface $symbol)
    {
        return $symbol->string();
    }

    private static $instance;
}
