<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Context;

use Eloquent\Cosmos\Symbol\SymbolInterface;
use Eloquent\Cosmos\UseStatement\UseStatementInterface;

/**
 * The interface implemented by symbol resolution contexts.
 */
interface ResolutionContextInterface
{
    /**
     * Get the namespace.
     *
     * @return SymbolInterface The namespace.
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
     * @param string $type The type.
     *
     * @return array<UseStatementInterface> The use statements.
     */
    public function useStatementsByType($type);

    /**
     * Get the symbol associated with the supplied symbol reference's first
     * atom.
     *
     * @param SymbolInterface $symbol The symbol reference.
     * @param string|null     $type   The symbol type.
     *
     * @return SymbolInterface|null The symbol, or null if no associated symbol exists.
     */
    public function symbolByFirstAtom(SymbolInterface $symbol, $type = null);
}
