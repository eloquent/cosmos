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
 *
 * @api
 */
interface ResolutionContextInterface
{
    /**
     * Get the namespace.
     *
     * @api
     *
     * @return SymbolInterface|null The namespace, or null if global.
     */
    public function primaryNamespace();

    /**
     * Get the use statements.
     *
     * @api
     *
     * @return array<UseStatementInterface> The use statements.
     */
    public function useStatements();

    /**
     * Get the use statements by type.
     *
     * @api
     *
     * @param string|null $type The type.
     *
     * @return array<UseStatementInterface> The use statements.
     */
    public function useStatementsByType($type);

    /**
     * Get the symbol associated with the supplied atom.
     *
     * @api
     *
     * @param string      $atom The atom.
     * @param string|null $type The symbol type.
     *
     * @return SymbolInterface|null The symbol, or null if no associated symbol exists.
     */
    public function symbolByAtom($atom, $type = null);

    /**
     * Get the string representation of this context.
     *
     * @api
     *
     * @return string The string representation.
     */
    public function __toString();
}
