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
use Eloquent\Cosmos\Exception\UndefinedClassException;
use Eloquent\Cosmos\Resolution\Context\ResolutionContextInterface;
use Eloquent\Cosmos\UseStatement\UseStatementInterface;
use ReflectionClass;

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

    /**
     * Construct a new class name resolution context by inspecting the source
     * code of the supplied object's class.
     *
     * @param object $object The object.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws SourceCodeReadException    If the source code cannot be read.
     */
    public function createFromObject($object);

    /**
     * Construct a new class name resolution context by inspecting the source
     * code of the supplied class.
     *
     * @param QualifiedClassNameInterface $className The class.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws UndefinedClassException    If the class does not exist.
     * @throws SourceCodeReadException    If the source code cannot be read.
     */
    public function createFromClass(QualifiedClassNameInterface $className);

    /**
     * Construct a new class name resolution context by inspecting the source
     * code of the supplied class reflector.
     *
     * @param ReflectionClass $reflector The reflector.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws SourceCodeReadException    If the source code cannot be read.
     */
    public function createFromReflector(ReflectionClass $reflector);
}
