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
 * The interface implemented by symbol resolution context factories.
 */
interface ResolutionContextFactoryInterface
{
    /**
     * Create a new symbol resolution context.
     *
     * @param SymbolInterface|null              $primaryNamespace The namespace.
     * @param array<UseStatementInterface>|null $useStatements    The use statements.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     */
    public function createContext(
        SymbolInterface $primaryNamespace = null,
        array $useStatements = null
    );
}
