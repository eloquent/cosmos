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
 * The interface implemented by resolution context generators.
 */
interface ResolutionContextGeneratorInterface
{
    /**
     * Generate a resolution context for importing the specified symbols.
     *
     * 'Keyword symbols' are typically constant and function symbols. They can
     * be provided to this method as an associative array, where the keys are
     * the relevant keywords (i.e. 'const' or 'function'), and the values are
     * arrays of symbols.
     *
     * @param SymbolInterface|null                      $namespace         The namespace, or null to use the global namespace.
     * @param array<SymbolInterface>|null               $symbols           The symbols to generate use statements for.
     * @param array<string,array<SymbolInterface>>|null $keywordSymbols    The keyword symbols to generate use statements for.
     * @param integer|null                              $maxReferenceAtoms The maximum number of atoms for symbol references.
     *
     * @return ResolutionContextInterface The generated resolution context.
     */
    public function generateContext(
        SymbolInterface $namespace = null,
        array $symbols = null,
        array $keywordSymbols = null,
        $maxReferenceAtoms = null
    );
}
