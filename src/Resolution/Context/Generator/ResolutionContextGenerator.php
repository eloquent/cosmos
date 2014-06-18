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
use Eloquent\Cosmos\UseStatement\UseStatementType;

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
     * @param QualifiedSymbolInterface|null        $primaryNamespace The namespace, or null to use the global namespace.
     * @param array<QualifiedSymbolInterface>|null $typeSymbols      The type symbols to generate use statements for.
     * @param array<QualifiedSymbolInterface>|null $functionSymbols  The function symbols to generate use statements for.
     * @param array<QualifiedSymbolInterface>|null $constantSymbols  The constant symbols to generate use statements for.
     *
     * @return ResolutionContextInterface The generated resolution context.
     */
    public function generate(
        QualifiedSymbolInterface $primaryNamespace = null,
        array $typeSymbols = null,
        array $functionSymbols = null,
        array $constantSymbols = null
    ) {
        if (null === $primaryNamespace) {
            $primaryNamespace = $this->symbolFactory()->globalNamespace();
        } else {
            $primaryNamespace = $primaryNamespace->normalize();
        }

        return $this->contextFactory()->create(
            $primaryNamespace,
            array_merge(
                $this->useStatementsForSymbols(
                    UseStatementType::TYPE(),
                    $primaryNamespace,
                    $typeSymbols
                ),
                $this->useStatementsForSymbols(
                    UseStatementType::FUNCT1ON(),
                    $primaryNamespace,
                    $functionSymbols
                ),
                $this->useStatementsForSymbols(
                    UseStatementType::CONSTANT(),
                    $primaryNamespace,
                    $constantSymbols
                )
            )
        );
    }

    /**
     * Generate a set of use statements for a set of symbols.
     *
     * @param UseStatementType                     $type             The use statement type.
     * @param QualifiedSymbolInterface             $primaryNamespace The namespace.
     * @param array<QualifiedSymbolInterface>|null $symbols          The symbols to generate use statements for.
     *
     * @return array<UseStatementInterface> The generated use statements.
     */
    private function useStatementsForSymbols(
        UseStatementType $type,
        QualifiedSymbolInterface $primaryNamespace,
        array $symbols = null
    ) {
        if (null === $symbols) {
            return array();
        }

        $useStatements = array();
        foreach ($symbols as $symbol) {
            $symbol = $symbol->normalize();

            if ($primaryNamespace->isAncestorOf($symbol)) {
                $numReferenceAtoms = count($symbol->atoms()) -
                    count($primaryNamespace->atoms());

                if ($numReferenceAtoms > $this->maxReferenceAtoms()) {
                    $useStatements[] = $this->useStatementFactory()
                        ->create($symbol, null, $type);
                }
            } else {
                $useStatements[] = $this->useStatementFactory()
                    ->create($symbol, null, $type);
            }
        }

        return $this->normalizeUseStatements($useStatements);
    }

    /**
     * Normalize a set of use statements by removing duplicates, sorting, and
     * generating aliases where necessary.
     *
     * @param array<UseStatementInterface> $useStatements The use statements to normalize.
     *
     * @return array<UseStatementInterface> The normalized use statements.
     */
    private function normalizeUseStatements(array $useStatements)
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

        $byAlias = $this->applyUseAliases($byAlias);
        foreach ($byAlias as $alias => $useStatements) {
            $byAlias[$alias] = array_pop($useStatements);
        }

        usort(
            $byAlias,
            function (
                UseStatementInterface $left,
                UseStatementInterface $right
            ) {
                return strcmp(
                    $left->symbol()->string(),
                    $right->symbol()->string()
                );
            }
        );

        return array_values($byAlias);
    }

    /**
     * Recursively find and resolve alias collisions.
     *
     * @param array<string,array<UseStatementInterface>> $byAlias An index of effective alias to use statements.
     * @param integer|null                               $level   The recursion level.
     *
     * @return array<string,array<UseStatementInterface>> The index with aliases applied.
     */
    private function applyUseAliases(array $byAlias, $level = null)
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
            return $this->applyUseAliases($byAlias, $level + 1);
        }

        return $byAlias;
    }

    private $maxReferenceAtoms;
    private $useStatementFactory;
    private $contextFactory;
    private $symbolFactory;
}
