<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution;

use Eloquent\Cosmos\ClassName\QualifiedClassNameInterface;
use Eloquent\Cosmos\Resolution\UseStatementInterface;

/**
 * The interface implemented by use statement generators.
 */
interface UseStatementGeneratorInterface
{
    /**
     * Generate a set of use statements for importing the specified classes.
     *
     * @param array<QualifiedClassNameInterface> $classNames       The classes to generate use statements for.
     * @param QualifiedClassNameInterface|null   $primaryNamespace The namespace, or null to use the global namespace.
     *
     * @return array<UseStatementInterface> The use statements.
     */
    public function generate(
        array $classNames,
        QualifiedClassNameInterface $primaryNamespace = null
    );
}
