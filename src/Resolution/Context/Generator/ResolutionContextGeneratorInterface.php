<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
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
     * @param array<QualifiedSymbolInterface> $symbols          The symbols to generate use statements for.
     * @param QualifiedSymbolInterface|null   $primaryNamespace The namespace, or null to use the global namespace.
     *
     * @return ResolutionContextInterface The generated resolution context.
     */
    public function generate(
        array $symbols,
        QualifiedSymbolInterface $primaryNamespace = null
    );
}
