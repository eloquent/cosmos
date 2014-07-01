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
use Eloquent\Cosmos\Symbol\SymbolInterface;
use Eloquent\Cosmos\Symbol\SymbolReferenceInterface;
use Eloquent\Cosmos\Symbol\SymbolType;
use Eloquent\Cosmos\UseStatement\UseStatementType;

/**
 * The interface implemented by symbol resolution contexts.
 */
interface ResolutionContextInterface extends ResolutionContextElementInterface
{
    /**
     * Get the namespace.
     *
     * @return QualifiedSymbolInterface The namespace.
     */
    public function primaryNamespace();

    /**
     * Get the use statements.
     *
     * @return array<UseStatementInterface> The use statements.
     */
    public function useStatements();

    /**
     * Get the use statements by type.
     *
     * @param UseStatementType $type The type.
     *
     * @return array<UseStatementInterface> The use statements.
     */
    public function useStatementsByType(UseStatementType $type);

    /**
     * Get the symbol associated with the supplied symbol reference's first
     * atom.
     *
     * @param SymbolReferenceInterface $symbol The symbol reference.
     * @param SymbolType|null          $type   The symbol type.
     *
     * @return QualifiedSymbolInterface|null The symbol, or null if no associated symbol exists.
     */
    public function symbolByFirstAtom(
        SymbolReferenceInterface $symbol,
        SymbolType $type = null
    );

    /**
     * Resolve a symbol against this resolution context.
     *
     * @param SymbolInterface $symbol The symbol to resolve.
     * @param SymbolType|null $type   The symbol type.
     *
     * @return QualifiedSymbolInterface The resolved symbol.
     */
    public function resolve(SymbolInterface $symbol, SymbolType $type = null);
}
