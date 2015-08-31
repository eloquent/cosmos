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

use Eloquent\Cosmos\Symbol\SymbolInterface;
use Eloquent\Cosmos\UseStatement\UseStatementFactory;
use Eloquent\Cosmos\UseStatement\UseStatementFactoryInterface;
use Eloquent\Cosmos\UseStatement\UseStatementNormalizer;
use Eloquent\Cosmos\UseStatement\UseStatementNormalizerInterface;

/**
 * Generates resolution contexts for importing sets of symbols.
 *
 * @api
 */
class ResolutionContextGenerator implements ResolutionContextGeneratorInterface
{
    /**
     * Get a static instance of this generator.
     *
     * @return ResolutionContextGeneratorInterface The static generator.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self(
                ResolutionContextFactory::instance(),
                UseStatementFactory::instance(),
                UseStatementNormalizer::instance()
            );
        }

        return self::$instance;
    }

    /**
     * Construct a new resolution context generator.
     *
     * @param ResolutionContextFactoryInterface $contextFactory         The resolution context factory to use.
     * @param UseStatementFactoryInterface      $useStatementFactory    The use statement factory to use.
     * @param UseStatementNormalizerInterface   $useStatementNormalizer The use statement normalizer to use.
     */
    public function __construct(
        ResolutionContextFactoryInterface $contextFactory,
        UseStatementFactoryInterface $useStatementFactory,
        UseStatementNormalizerInterface $useStatementNormalizer
    ) {
        $this->contextFactory = $contextFactory;
        $this->useStatementFactory = $useStatementFactory;
        $this->useStatementNormalizer = $useStatementNormalizer;
    }

    /**
     * Generate a resolution context for importing the specified symbols.
     *
     * 'Keyword symbols' are typically constant and function symbols. They can
     * be provided to this method as an associative array, where the keys are
     * the relevant keywords (i.e. 'const' or 'function'), and the values are
     * arrays of symbols.
     *
     * @api
     *
     * @param SymbolInterface|null                      $namespace         The namespace, or null to use the global namespace.
     * @param array<SymbolInterface>|null               $symbols           The symbols to generate use statements for.
     * @param array<string,array<SymbolInterface>>|null $keywordSymbols    The keyword symbols to generate use statements for.
     * @param integer|null                              $maxReferenceAtoms The maximum number of atoms for symbol references.
     *
     * @return ResolutionContextInterface The generated resolution context.
     */
    public function generateContext(
        SymbolInterface $namespace = null,
        array $symbols = null,
        array $keywordSymbols = null,
        $maxReferenceAtoms = null
    ) {
        if (null === $symbols) {
            $symbols = array();
        }
        if (null === $keywordSymbols) {
            $keywordSymbols = array();
        }
        if (null === $maxReferenceAtoms) {
            $maxReferenceAtoms = 1;
        }

        if (null === $namespace) {
            $namespaceAtoms = array();
            $numNamespaceAtoms = 0;
        } else {
            $namespaceAtoms = $namespace->atoms();
            $numNamespaceAtoms = count($namespaceAtoms);
        }

        $statements = $this->forSymbols(
            null,
            $namespaceAtoms,
            $numNamespaceAtoms,
            $symbols,
            $maxReferenceAtoms
        );

        foreach ($keywordSymbols as $type => $typeSymbols) {
            $statements = \array_merge(
                $statements,
                $this->forSymbols(
                    $type,
                    $namespaceAtoms,
                    $numNamespaceAtoms,
                    $typeSymbols,
                    $maxReferenceAtoms
                )
            );
        }

        return $this->contextFactory->createContext(
            $namespace,
            $this->useStatementNormalizer->normalizeStatements($statements)
        );
    }

    private function forSymbols(
        $type,
        $namespaceAtoms,
        $numNamespaceAtoms,
        $symbols,
        $maxReferenceAtoms
    ) {
        $clauses = array();
        $seen = array();

        foreach ($symbols as $symbol) {
            $key = \strval($symbol);

            if (isset($seen[$key])) {
                // duplicate symbol
                continue;
            }

            $seen[$key] = $symbol;

            $atoms = $symbol->atoms();
            $useStatementRequired = true;
            $isDescendant =
                $namespaceAtoms === \array_slice($atoms, 0, $numNamespaceAtoms);

            if ($isDescendant) {
                // symbol is descendant of namespace
                $numReferenceAtoms = \count($atoms) - $numNamespaceAtoms;

                if ($numReferenceAtoms <= $maxReferenceAtoms) {
                    // reference is within maximum size
                    $useStatementRequired = false;
                }
            }

            if ($useStatementRequired) {
                $clauses[] = $this->useStatementFactory->createClause($symbol);
            }
        }

        $byAlias = array();

        // group clauses by alias
        foreach ($clauses as $clause) {
            $alias = $clause->effectiveAlias();

            if (!isset($byAlias[$alias])) {
                $byAlias[$alias] = array();
            }

            $byAlias[$alias][] = $clause;
        }

        $statements = array();

        foreach ($this->applyAliases($byAlias, 0) as $clause) {
            $statements[] = $this->useStatementFactory
                ->createStatement(array($clause), $type);
        }

        return $statements;
    }

    private function applyAliases($byAlias, $level)
    {
        $changes = false;

        foreach ($byAlias as $alias => $clauses) {
            $numClauses = \count($clauses);

            if ($numClauses < 2) {
                continue;
            }

            foreach ($clauses as $index => $clause) {
                $clauseSymbol = $clause->symbol();
                $clauseAtoms = $clauseSymbol->atoms();
                $startIndex = \count($clauseAtoms) - ($level + 2);

                if ($startIndex < 0) {
                    continue;
                }

                $changes = true;

                $currentAlias = $clause->effectiveAlias();
                $newAlias = $clauseAtoms[$startIndex] . $currentAlias;
                $clause = $this->useStatementFactory
                    ->createClause($clauseSymbol, $newAlias);

                unset($clauses[$index]);
                --$numClauses;

                if (!isset($byAlias[$newAlias])) {
                    $byAlias[$newAlias] = array();
                }

                $byAlias[$newAlias][] = $clause;
            }

            if ($numClauses > 0) {
                $byAlias[$alias] = \array_values($clauses);
            } else {
                unset($byAlias[$alias]);
            }
        }

        if ($changes) {
            return $this->applyAliases($byAlias, $level + 1);
        }

        foreach ($byAlias as $alias => $clauses) {
            $byAlias[$alias] = \array_pop($clauses);
        }

        return $byAlias;
    }

    private static $instance;
    private $useStatementFactory;
    private $useStatementNormalizer;
    private $contextFactory;
}
