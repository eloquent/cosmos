<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution;

use Eloquent\Cosmos\ClassName\Factory\ClassNameFactory;
use Eloquent\Cosmos\ClassName\Factory\ClassNameFactoryInterface;
use Eloquent\Cosmos\ClassName\QualifiedClassNameInterface;
use Eloquent\Cosmos\UseStatement\UseStatementInterface;

/**
 * Creates class name resolution contexts.
 */
class ResolutionContextFactory implements ResolutionContextFactoryInterface
{
    /**
     * Get a static instance of this factory.
     *
     * @return ResolutionContextFactoryInterface The static factory.
     */
    public static function instance()
    {
        if (null === static::$instance) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    /**
     * Construct a new class name resolution context factory.
     *
     * @param ClassNameFactoryInterface $classNameFactory The class name factory to use.
     */
    public function __construct(
        ClassNameFactoryInterface $classNameFactory = null
    ) {
        if (null === $classNameFactory) {
            $classNameFactory = ClassNameFactory::instance();
        }

        $this->classNameFactory = $classNameFactory;
    }

    /**
     * Get the class name factory.
     *
     * @return ClassNameFactoryInterface The class name factory.
     */
    public function classNameFactory()
    {
        return $this->classNameFactory;
    }

    /**
     * Construct a new class name resolution context.
     *
     * @param QualifiedClassNameInterface|null  $primaryNamespace The namespace.
     * @param array<UseStatementInterface>|null $useStatements    The use statements.
     */
    public function create(
        QualifiedClassNameInterface $primaryNamespace = null,
        array $useStatements = null
    ) {
        return new ResolutionContext(
            $primaryNamespace,
            $useStatements,
            $this->classNameFactory()
        );
    }

    private static $instance;
    private $classNameFactory;
}
