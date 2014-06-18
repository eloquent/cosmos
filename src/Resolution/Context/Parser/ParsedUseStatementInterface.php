<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Context\Parser;

use Eloquent\Cosmos\UseStatement\UseStatementInterface;

/**
 * The interface implemented by parsed use statements.
 */
interface ParsedUseStatementInterface extends
    UseStatementInterface,
    ParsedElementInterface
{
    /**
     * Get the use statement.
     *
     * @return UseStatementInterface The use statement.
     */
    public function useStatement();
}
