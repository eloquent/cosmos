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

/**
 * The interface implemented by symbol resolution context renderers.
 *
 * @api
 */
interface ResolutionContextRendererInterface
{
    /**
     * Render a symbol resolution context.
     *
     * @api
     *
     * @param ResolutionContextInterface $context The context to render.
     *
     * @return string The rendered context.
     */
    public function renderContext(ResolutionContextInterface $context);
}
