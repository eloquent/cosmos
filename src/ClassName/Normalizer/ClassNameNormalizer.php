<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\ClassName\Normalizer;

use Eloquent\Cosmos\ClassName\Factory\ClassNameFactory;
use Eloquent\Cosmos\ClassName\Factory\ClassNameFactoryInterface;
use Eloquent\Pathogen\Normalizer\PathNormalizer;

/**
 * Normalizes class names.
 */
class ClassNameNormalizer extends PathNormalizer implements
    ClassNameNormalizerInterface
{
    /**
     * Get a static instance of this normalizer.
     *
     * @return ClassNameNormalizerInterface The static normalizer.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Construct a new class name normalizer.
     *
     * @param ClassNameFactoryInterface|null $factory The class name factory to use.
     */
    public function __construct(ClassNameFactoryInterface $factory = null)
    {
        if (null === $factory) {
            $factory = ClassNameFactory::instance();
        }

        parent::__construct($factory);
    }

    private static $instance;
}
