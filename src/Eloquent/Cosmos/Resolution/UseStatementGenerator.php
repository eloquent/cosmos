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

use Eloquent\Cosmos\ClassName\Factory\ClassNameFactory;
use Eloquent\Cosmos\ClassName\Factory\ClassNameFactoryInterface;
use Eloquent\Cosmos\ClassName\QualifiedClassNameInterface;
use Eloquent\Cosmos\Resolution\UseStatementInterface;

/**
 * Generates use statements for importing sets of classes.
 */
class UseStatementGenerator implements UseStatementGeneratorInterface
{
    /**
     * Construct a new use statement generator.
     *
     * @param integer|null                   $maxReferenceAtoms The maximum acceptable number of atoms for class references relative to the namespace.
     * @param ClassNameFactoryInterface|null $factory           The class name factory to use.
     */
    public function __construct(
        $maxReferenceAtoms = null,
        ClassNameFactoryInterface $factory = null
    ) {
        if (null === $maxReferenceAtoms) {
            $maxReferenceAtoms = 2;
        }
        if (null === $factory) {
            $factory = new ClassNameFactory;
        }

        $this->maxReferenceAtoms = $maxReferenceAtoms;
        $this->factory = $factory;
    }

    /**
     * Get the maximum acceptable number of atoms for class references relative
     * to the namespace.
     *
     * @return integer The maximum number of atoms.
     */
    public function maxReferenceAtoms()
    {
        return $this->maxReferenceAtoms;
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
     * Generate a set of use statements for importing the specified classes.
     *
     * @param array<QualifiedClassNameInterface> $classNames       The classes to generate use statements for.
     * @param QualifiedClassNameInterface|null   $primaryNamespace The namespace, or null to use the global namespace.
     *
     * @return array<UseStatementInterface> The use statements.
     */
    public function generate(
        array $classNames,
        QualifiedClassNameInterface $primaryNamespace = null
    ) {
        if (null === $primaryNamespace) {
            $primaryNamespace = $this->factory()
                ->createFromAtoms(array(), true);
        } else {
            $primaryNamespace = $primaryNamespace->normalize();
        }

        $useStatements = array();
        foreach ($classNames as $className) {
            $className = $className->normalize();

            if ($primaryNamespace->isAncestorOf($className)) {
                $numReferenceAtoms = count($className->atoms()) -
                    count($primaryNamespace->atoms());
                if ($numReferenceAtoms > $this->maxReferenceAtoms()) {
                    $useStatements[] = new UseStatement($className);
                }
            } else {
                $useStatements[] = new UseStatement($className);
            }
        }

        return $this->normalize($useStatements);
    }

    /**
     * Normalize a set of use statements by removing duplicates, sorting, and
     * generating aliases where necessary.
     *
     * @param array<UseStatementInterface> $useStatements The use statements to normalize.
     *
     * @return array<UseStatementInterface> The normalized use statements.
     */
    protected function normalize(array $useStatements)
    {
        $normalized = array();
        $byAlias = array();

        foreach ($useStatements as $index => $useStatement) {
            $key = $useStatement->string();
            if (array_key_exists($key, $normalized)) {
                continue;
            }

            $normalized[$key] = $useStatement;

            $aliasString = $useStatement->effectiveAlias()->string();
            if (!array_key_exists($aliasString, $byAlias)) {
                $byAlias[$aliasString] = array();
            }
            $byAlias[$aliasString][] = $useStatement;
        }

        $this->applyAliases($byAlias);

        usort(
            $normalized,
            function (
                UseStatementInterface $left,
                UseStatementInterface $right
            ) {
                return strcmp($left->string(), $right->string());
            }
        );

        return $normalized;
    }

    /**
     * Recursively find and resolve alias collisions.
     *
     * @param array<string,UseStatementInterface> $byAlias An index of effective alias to use statements.
     * @param integer|null                        $level   The recursion level.
     */
    protected function applyAliases(array $byAlias, $level = null)
    {
        if (null === $level) {
            $level = 0;
        }

        $changes = false;
        foreach ($byAlias as $alias => $useStatements) {
            if (count($useStatements) < 2) {
                continue;
            }

            foreach ($useStatements as $index => $useStatement) {
                $startIndex = count($useStatement->className()->atoms()) -
                    ($level + 2);
                if ($startIndex < 0) {
                    continue;
                }

                $changes = true;

                $prefixAtoms = $useStatement->className()
                    ->sliceAtoms($startIndex, 1);
                $prefix = array_pop($prefixAtoms);
                $currentAlias = $useStatement->effectiveAlias()->name();
                $newAlias = $this->factory()->createFromAtoms(
                    array($prefix . $currentAlias),
                    false
                );
                $useStatement->setAlias($newAlias);

                unset($useStatements[$index]);

                $aliasString = $newAlias->string();
                if (!array_key_exists($aliasString, $byAlias)) {
                    $byAlias[$aliasString] = array();
                }
                $byAlias[$aliasString][] = $useStatement;
            }

            $byAlias[$alias] = $useStatements;
        }

        if ($changes) {
            $this->applyAliases($byAlias, $level + 1);
        }
    }

    private $maxReferenceAtoms;
    private $factory;
}
