<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Context\Generator;

use Eloquent\Cosmos\Resolution\Context\ResolutionContextInterface;
use Eloquent\Cosmos\Symbol\QualifiedSymbolInterface;

/**
 * The interface implemented by resolution context generators.
 */
interface ResolutionContextGeneratorInterface
{
    /**
     * Generate a resolution context for importing the specified symbols.
     *
     * @param QualifiedSymbolInterface|null        $primaryNamespace The namespace, or null to use the global namespace.
     * @param array<QualifiedSymbolInterface>|null $typeSymbols      The type symbols to generate use statements for.
     * @param array<QualifiedSymbolInterface>|null $functionSymbols  The function symbols to generate use statements for.
     * @param array<QualifiedSymbolInterface>|null $constantSymbols  The constant symbols to generate use statements for.
     *
     * @return ResolutionContextInterface The generated resolution context.
     */
    public function generate(
        QualifiedSymbolInterface $primaryNamespace = null,
        array $typeSymbols = null,
        array $functionSymbols = null,
        array $constantSymbols = null
    );
}
