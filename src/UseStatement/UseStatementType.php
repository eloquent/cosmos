<?php // @codeCoverageIgnoreStart

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\UseStatement;

use Eloquent\Enumeration\AbstractEnumeration;

/**
 * An enumeration of available use statement types.
 */
final class UseStatementType extends AbstractEnumeration
{
    /**
     * A regular use statement, used for importing classes, interfaces, and traits.
     */
    const TYPE = 'type';

    /**
     * A function use statement.
     */
    const FUNCT1ON = 'function';

    /**
     * A constant use statement.
     */
    const CONSTANT = 'const';
}
