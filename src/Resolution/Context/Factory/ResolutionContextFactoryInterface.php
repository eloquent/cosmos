<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Context\Factory;

use Eloquent\Cosmos\ClassName\QualifiedClassNameInterface;
use Eloquent\Cosmos\Resolution\Context\ResolutionContextInterface;
use Eloquent\Cosmos\UseStatement\UseStatementInterface;

/**
 * The interface implemented by class name resolution context factories.
 */
interface ResolutionContextFactoryInterface
{
    /**
     * Construct a new class name resolution context.
     *
     * @param QualifiedClassNameInterface|null  $primaryNamespace The namespace.
     * @param array<UseStatementInterface>|null $useStatements    The use statements.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     */
    public function create(
        QualifiedClassNameInterface $primaryNamespace = null,
        array $useStatements = null
    );
}
