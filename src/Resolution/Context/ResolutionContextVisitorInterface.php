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

use Eloquent\Cosmos\Symbol\QualifiedSymbolInterface;
use Eloquent\Cosmos\Symbol\SymbolReferenceInterface;
use Eloquent\Cosmos\UseStatement\UseStatementInterface;

/**
 * The interface implemented by symbol resolution context visitors.
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
     * Visit a qualified symbol.
     *
     * @param QualifiedSymbolInterface $symbol The symbol to visit.
     *
     * @return mixed The result of visitation.
     */
    public function visitQualifiedSymbol(QualifiedSymbolInterface $symbol);

    /**
     * Visit a symbol reference.
     *
     * @param SymbolReferenceInterface $symbol The symbol to visit.
     *
     * @return mixed The result of visitation.
     */
    public function visitSymbolReference(SymbolReferenceInterface $symbol);
}
