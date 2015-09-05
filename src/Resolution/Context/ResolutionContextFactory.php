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

/**
 * Creates symbol resolution contexts.
 *
 * @api
 */
class ResolutionContextFactory implements ResolutionContextFactoryInterface
{
    /**
     * Get a static instance of this factory.
     *
     * @api
     *
     * @return ResolutionContextFactoryInterface The static factory.
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Create a new symbol resolution context.
     *
     * @api
     *
     * @param SymbolInterface|null         $primaryNamespace The namespace, or null for the global namespace.
     * @param array<UseStatementInterface> $useStatements    The use statements.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     */
    public function createContext(
        SymbolInterface $primaryNamespace = null,
        array $useStatements = array()
    ) {
        return new ResolutionContext($primaryNamespace, $useStatements);
    }

    private static $instance;
}
