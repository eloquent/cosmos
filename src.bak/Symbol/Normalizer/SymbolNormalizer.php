<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Symbol\Normalizer;

use Eloquent\Cosmos\Symbol\Factory\SymbolFactory;
use Eloquent\Cosmos\Symbol\Factory\SymbolFactoryInterface;
use Eloquent\Pathogen\Normalizer\PathNormalizer;

/**
 * Normalizes symbols.
 */
class SymbolNormalizer extends PathNormalizer implements
    SymbolNormalizerInterface
{
    /**
     * Get a static instance of this normalizer.
     *
     * @return SymbolNormalizerInterface The static normalizer.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Construct a new symbol normalizer.
     *
     * @param SymbolFactoryInterface|null $factory The symbol factory to use.
     */
    public function __construct(SymbolFactoryInterface $factory = null)
    {
        if (null === $factory) {
            $factory = SymbolFactory::instance();
        }

        parent::__construct($factory);
    }

    private static $instance;
}
