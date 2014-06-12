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

use Eloquent\Cosmos\ClassName\QualifiedClassNameInterface;
use Eloquent\Cosmos\Resolution\Context\ResolutionContextInterface;

/**
 * The interface implemented by resolution context generators.
 */
interface ResolutionContextGeneratorInterface
{
    /**
     * Generate a resolution context for importing the specified classes.
     *
     * @param array<QualifiedClassNameInterface> $classNames       The classes to generate use statements for.
     * @param QualifiedClassNameInterface|null   $primaryNamespace The namespace, or null to use the global namespace.
     *
     * @return ResolutionContextInterface The generated resolution context.
     */
    public function generate(
        array $classNames,
        QualifiedClassNameInterface $primaryNamespace = null
    );
}
