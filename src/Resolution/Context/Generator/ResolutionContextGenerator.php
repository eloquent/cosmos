<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Context\Generator;

use Eloquent\Cosmos\Resolution\Context\Factory\ResolutionContextFactory;
use Eloquent\Cosmos\Resolution\Context\Factory\ResolutionContextFactoryInterface;
use Eloquent\Cosmos\Resolution\Context\ResolutionContextInterface;
use Eloquent\Cosmos\Symbol\Factory\SymbolFactory;
use Eloquent\Cosmos\Symbol\Factory\SymbolFactoryInterface;
use Eloquent\Cosmos\Symbol\QualifiedSymbolInterface;
use Eloquent\Cosmos\UseStatement\Factory\UseStatementFactory;
use Eloquent\Cosmos\UseStatement\Factory\UseStatementFactoryInterface;
use Eloquent\Cosmos\UseStatement\UseStatement;
use Eloquent\Cosmos\UseStatement\UseStatementInterface;

/**
 * Generates resolution contexts for importing sets of symbols.
 */
class ResolutionContextGenerator implements ResolutionContextGeneratorInterface
{
    /**
     * Construct a new resolution context generator.
     *
     * @param integer|null                           $maxReferenceAtoms   The maximum acceptable number of atoms for symbol references relative to the namespace.
     * @param ResolutionContextFactoryInterface|null $contextFactory      The resolution context factory to use.
     * @param UseStatementFactoryInterface|null      $useStatementFactory The use statement factory to use.
     * @param SymbolFactoryInterface|null            $symbolFactory       The symbol factory to use.
     */
    public function __construct(
        $maxReferenceAtoms = null,
        ResolutionContextFactoryInterface $contextFactory = null,
        UseStatementFactoryInterface $useStatementFactory = null,
        SymbolFactoryInterface $symbolFactory = null
    ) {
        if (null === $maxReferenceAtoms) {
            $maxReferenceAtoms = 1;
        }
        if (null === $contextFactory) {
            $contextFactory = ResolutionContextFactory::instance();
        }
        if (null === $useStatementFactory) {
            $useStatementFactory = UseStatementFactory::instance();
        }
        if (null === $symbolFactory) {
            $symbolFactory = SymbolFactory::instance();
        }

        $this->maxReferenceAtoms = $maxReferenceAtoms;
        $this->contextFactory = $contextFactory;
        $this->useStatementFactory = $useStatementFactory;
        $this->symbolFactory = $symbolFactory;
    }

    /**
     * Get the maximum acceptable number of atoms for symbol references relative
     * to the namespace.
     *
     * @return integer The maximum number of atoms.
     */
    public function maxReferenceAtoms()
    {
        return $this->maxReferenceAtoms;
    }

    /**
     * Get the resolution context factory.
     *
     * @return ResolutionContextFactoryInterface The resolution context factory.
     */
    public function contextFactory()
    {
        return $this->contextFactory;
    }

    /**
     * Get the use statement factory.
     *
     * @return UseStatementFactoryInterface The use statement factory to use.
     */
    public function useStatementFactory()
    {
        return $this->useStatementFactory;
    }

    /**
     * Get the symbol factory.
     *
     * @return SymbolFactoryInterface The symbol factory.
     */
    public function symbolFactory()
    {
        return $this->symbolFactory;
    }

    /**
     * Generate a resolution context for importing the specified symbols.
     *
     * @param array<QualifiedSymbolInterface> $symbols          The symbols to generate use statements for.
     * @param QualifiedSymbolInterface|null   $primaryNamespace The namespace, or null to use the global namespace.
     *
     * @return ResolutionContextInterface The generated resolution context.
     */
    public function generate(
        array $symbols,
        QualifiedSymbolInterface $primaryNamespace = null
    ) {
        if (null === $primaryNamespace) {
            $primaryNamespace = $this->symbolFactory()->globalNamespace();
        } else {
            $primaryNamespace = $primaryNamespace->normalize();
        }

        $useStatements = array();
        foreach ($symbols as $symbol) {
            $symbol = $symbol->normalize();

            if ($primaryNamespace->isAncestorOf($symbol)) {
                $numReferenceAtoms = count($symbol->atoms()) -
                    count($primaryNamespace->atoms());
                if ($numReferenceAtoms > $this->maxReferenceAtoms()) {
                    $useStatements[] = $this->useStatementFactory()
                        ->create($symbol);
                }
            } else {
                $useStatements[] = $this->useStatementFactory()
                    ->create($symbol);
            }
        }

        return $this->contextFactory()
            ->create($primaryNamespace, $this->normalize($useStatements));
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
        $seen = array();
        $byAlias = array();
        foreach ($useStatements as $index => $useStatement) {
            $key = $useStatement->string();
            if (array_key_exists($key, $seen)) {
                continue;
            }

            $seen[$key] = true;

            $aliasString = $useStatement->effectiveAlias()->string();
            if (!array_key_exists($aliasString, $byAlias)) {
                $byAlias[$aliasString] = array();
            }
            $byAlias[$aliasString][] = $useStatement;
        }

        $byAlias = $this->applyAliases($byAlias);

        $normalized = array();
        foreach ($byAlias as $useStatements) {
            list($useStatement) = $useStatements;
            $key = $useStatement->string();

            $normalized[$key] = $useStatement;
        }
        ksort($normalized);

        return $normalized;
    }

    /**
     * Recursively find and resolve alias collisions.
     *
     * @param array<string,UseStatementInterface> $byAlias An index of effective alias to use statements.
     * @param integer|null                        $level   The recursion level.
     *
     * @return array<string,UseStatementInterface> The index with aliases applied.
     */
    protected function applyAliases(array $byAlias, $level = null)
    {
        if (null === $level) {
            $level = 0;
        }

        $changes = false;
        foreach ($byAlias as $alias => $useStatements) {
            $numUseStatements = count($useStatements);
            if ($numUseStatements < 2) {
                continue;
            }

            foreach ($useStatements as $index => $useStatement) {
                $startIndex = count($useStatement->symbol()->atoms()) -
                    ($level + 2);
                if ($startIndex < 0) {
                    continue;
                }

                $changes = true;

                $currentAlias = $useStatement->effectiveAlias()->name();
                $newAlias = $this->symbolFactory()->createFromAtoms(
                    array(
                        $useStatement->symbol()->atomAt($startIndex) .
                        $currentAlias
                    ),
                    false
                );
                $useStatement = $this->useStatementFactory()->create(
                    $useStatement->symbol(),
                    $newAlias,
                    $useStatement->type()
                );

                unset($useStatements[$index]);
                $numUseStatements--;

                $aliasString = $newAlias->string();
                if (!array_key_exists($aliasString, $byAlias)) {
                    $byAlias[$aliasString] = array();
                }
                $byAlias[$aliasString][] = $useStatement;
            }

            if ($numUseStatements > 0) {
                $byAlias[$alias] = array_values($useStatements);
            } else {
                unset($byAlias[$alias]);
            }
        }

        if ($changes) {
            return $this->applyAliases($byAlias, $level + 1);
        }

        return $byAlias;
    }

    private $maxReferenceAtoms;
    private $useStatementFactory;
    private $contextFactory;
    private $symbolFactory;
}
