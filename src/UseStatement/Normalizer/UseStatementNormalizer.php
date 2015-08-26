<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\UseStatement\Normalizer;

use Eloquent\Cosmos\UseStatement\Factory\UseStatementFactory;
use Eloquent\Cosmos\UseStatement\Factory\UseStatementFactoryInterface;
use Eloquent\Cosmos\UseStatement\UseStatementType;

/**
 * The interface implemented by use statement normalizers.
 */
class UseStatementNormalizer implements UseStatementNormalizerInterface
{
    /**
     * Get a static instance of this normalizer.
     *
     * @return UseStatementNormalizerInterface The static normalizer.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Construct a new use statement normalizer.
     *
     * @param UseStatementFactoryInterface|null $useStatementFactory The use statement factory to use.
     */
    public function __construct(
        UseStatementFactoryInterface $useStatementFactory = null
    ) {
        if (null === $useStatementFactory) {
            $useStatementFactory = UseStatementFactory::instance();
        }

        $this->useStatementFactory = $useStatementFactory;
    }

    /**
     * Get the use statement factory.
     *
     * @return UseStatementFactoryInterface The use statement factory.
     */
    public function useStatementFactory()
    {
        return $this->useStatementFactory;
    }

    /**
     * Normalize the supplied use statements.
     *
     * @param array<UseStatementInterface> $useStatements The use statements to normalize.
     *
     * @return array<UseStatementInterface> The normalized use statements.
     */
    public function normalize(array $useStatements)
    {
        $typeType = UseStatementType::TYPE();
        $functionType = UseStatementType::FUNCT1ON();
        $constantType = UseStatementType::CONSTANT();
        $typeClauses = array();
        $functionClauses = array();
        $constantClauses = array();

        foreach ($useStatements as $useStatement) {
            if ($functionType === $useStatement->type()) {
                $functionClauses = array_merge(
                    $functionClauses,
                    $useStatement->clauses()
                );
            } elseif ($constantType === $useStatement->type()) {
                $constantClauses = array_merge(
                    $constantClauses,
                    $useStatement->clauses()
                );
            } else {
                $typeClauses = array_merge(
                    $typeClauses,
                    $useStatement->clauses()
                );
            }
        }

        $useStatements = array();
        foreach ($this->normalizeClauses($typeClauses) as $clause) {
            $useStatements[] = $this->useStatementFactory()
                ->createStatement(array($clause), $typeType);
        }
        foreach ($this->normalizeClauses($functionClauses) as $clause) {
            $useStatements[] = $this->useStatementFactory()
                ->createStatement(array($clause), $functionType);
        }
        foreach ($this->normalizeClauses($constantClauses) as $clause) {
            $useStatements[] = $this->useStatementFactory()
                ->createStatement(array($clause), $constantType);
        }

        return $useStatements;
    }

    /**
     * Normalize the supplied use statement clauses.
     *
     * @param array<UseStatementClauseInterface> $useStatementClauses The use statement clauses to normalize.
     *
     * @return array<UseStatementClauseInterface> The normalized use statement clauses.
     */
    public function normalizeClauses(array $useStatementClauses)
    {
        $normalized = array();
        $seen = array();
        foreach ($useStatementClauses as $clause) {
            $key = $clause->string();
            if (array_key_exists($key, $seen)) {
                continue;
            }

            $seen[$key] = true;
            $normalized[] = $clause;
        }

        usort($normalized, 'strcmp');

        return $normalized;
    }

    private static $instance;
    private $useStatementFactory;
}
