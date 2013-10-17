<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Cosmos\ClassName\Factory\Consumer;

use Eloquent\Cosmos\ClassName\Factory\ClassNameFactory;
use Eloquent\Cosmos\ClassName\Factory\ClassNameFactoryInterface;

/**
 * A trait for classes that take a class name factory as a dependency.
 */
trait ClassNameFactoryTrait
{
    /**
     * Set the class name factory.
     *
     * @param ClassNameFactoryInterface $classNameFactory
     */
    public function setClassNameFactory(
        ClassNameFactoryInterface $classNameFactory
    ) {
        $this->classNameFactory = $classNameFactory;
    }

    /**
     * Get the class name factory.
     *
     * @return ClassNameFactoryInterface The class name factory.
     */
    public function classNameFactory()
    {
        if (null === $this->classNameFactory) {
            $this->classNameFactory = $this->createDefaultClassNameFactory();
        }

        return $this->classNameFactory;
    }

    /**
     * Create a default class name factory.
     *
     * @return ClassNameFactoryInterface The new class name factory.
     */
    protected function createDefaultClassNameFactory()
    {
        return new ClassNameFactory;
    }

    private $classNameFactory;
}
