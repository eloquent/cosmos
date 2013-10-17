<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution;

use Eloquent\Cosmos\ClassName\ClassNameReference;
use Eloquent\Cosmos\ClassName\ClassNameReferenceInterface;
use Eloquent\Cosmos\ClassName\Factory\ClassNameFactory;
use Eloquent\Cosmos\ClassName\Factory\ClassNameFactoryInterface;
use Eloquent\Cosmos\ClassName\QualifiedClassNameInterface;

/**
 * Represents a combined namespace and set of use statements.
 */
class ResolutionContext implements ResolutionContextInterface
{
    /**
     * Construct a new class name resolution context.
     *
     * @param QualifiedClassNameInterface|null  $primaryNamespace The namespace.
     * @param array<UseStatementInterface>|null $useStatements    The use statements.
     * @param ClassNameFactoryInterface|null    $factory          The class name factory to use.
     */
    public function __construct(
        QualifiedClassNameInterface $primaryNamespace = null,
        array $useStatements = null,
        ClassNameFactoryInterface $factory = null
    ) {
        if (null === $factory) {
            $factory = new ClassNameFactory;
        }
        if (null === $primaryNamespace) {
            $primaryNamespace = $factory->createFromAtoms(array(), true);
        }
        if (null === $useStatements) {
            $useStatements = array();
        }

        $this->primaryNamespace = $primaryNamespace;
        $this->useStatements = $useStatements;
        $this->factory = $factory;
    }

    /**
     * Get the namespace.
     *
     * @return QualifiedClassNameInterface The namespace.
     */
    public function primaryNamespace()
    {
        return $this->primaryNamespace;
    }

    /**
     * Get the use statements.
     *
     * @return array<UseStatementInterface> The use statements.
     */
    public function useStatements()
    {
        return $this->useStatements;
    }

    /**
     * Resolve a class name reference against this context.
     *
     * @param ClassNameReferenceInterface $reference The reference to resolve.
     *
     * @return QualifiedClassNameInterface The resolved, qualified class name.
     */
    public function resolve(ClassNameReferenceInterface $reference)
    {
        $atoms = $reference->atoms();
        $numAtoms = count($atoms);

        $firstAtom = null;
        if ($numAtoms > 0) {
            $firstAtom = $atoms[0];
        }

        if (
            null !== $firstAtom &&
            ClassNameReference::PARENT_ATOM !== $firstAtom
        ) {
            $index = $this->index();
            if (array_key_exists($firstAtom, $index)) {
                $parent = $index[$firstAtom];

                if ($numAtoms < 2) {
                    return $parent;
                }

                return $parent->joinAtomSequence($reference->sliceAtoms(1))
                    ->normalize();
            }
        }

        return $this->primaryNamespace()->join($reference)->normalize();
    }

    /**
     * Get the class name factory.
     *
     * @return ClassNameFactoryInterface The class name factory.
     */
    public function factory()
    {
        return $this->factory;
    }

    /**
     * Sets the internal class name index for resolving class name references.
     *
     * @param array $index The index.
     */
    protected function setIndex(array $index)
    {
        $this->index = $index;
    }

    /**
     * Get an index for resolving class name references.
     *
     * The first time this method is called, the index will be built.
     *
     * @return array The index.
     */
    protected function index()
    {
        if (null === $this->index) {
            $this->setIndex($this->buildIndex());
        }

        return $this->index;
    }

    /**
     * Builds the internal index used to resolve class name references.
     *
     * @return array The index.
     */
    protected function buildIndex()
    {
        $index = array();
        foreach ($this->useStatements as $useStatement) {
            if (null === $useStatement->alias()) {
                $alias = $useStatement->className()->name();
            } else {
                $alias = $useStatement->alias()->string();
            }

            $index[$alias] = $useStatement->className();
        }

        return $index;
    }

    private $primaryNamespace;
    private $useStatements;
    private $factory;
    private $index;
}
