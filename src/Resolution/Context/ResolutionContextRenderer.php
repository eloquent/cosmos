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
 * Renders resolution contexts using standard PHP syntax.
 *
 * @api
 */
class ResolutionContextRenderer implements ResolutionContextRendererInterface
{
    /**
     * Get a static instance of this renderer.
     *
     * @api
     *
     * @return ResolutionContextRendererInterface The static renderer.
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }
    /**
     * Render a symbol resolution context.
     *
     * @api
     *
     * @param ResolutionContextInterface $context The context to render.
     *
     * @return string The rendered context.
     */
    public function renderContext(ResolutionContextInterface $context)
    {
        return \strval($context);
    }

    private static $instance;
}
