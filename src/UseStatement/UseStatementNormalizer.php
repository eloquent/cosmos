<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\UseStatement;

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
            self::$instance = new self(UseStatementFactory::instance());
        }

        return self::$instance;
    }

    /**
     * Construct a new use statement normalizer.
     *
     * @param UseStatementFactoryInterface $useStatementFactory The use statement factory to use.
     */
    public function __construct(
        UseStatementFactoryInterface $useStatementFactory
    ) {
        $this->useStatementFactory = $useStatementFactory;
    }

    /**
     * Normalize the supplied use statements.
     *
     * @param array<UseStatementInterface> $statements The use statements to normalize.
     *
     * @return array<UseStatementInterface> The normalized use statements.
     */
    public function normalizeStatements(array $statements)
    {
        $clauses = array();
        $clausesByType = array();

        foreach ($statements as $statement) {
            $type = $statement->type();

            if (null === $type) {
                $clauses = array_merge($clauses, $statement->clauses());
            } else {
                if (!isset($clausesByType[$type])) {
                    $clausesByType[$type] = array();
                }

                $clausesByType[$type] =
                    array_merge($clausesByType[$type], $statement->clauses());
            }
        }

        ksort($clausesByType, SORT_STRING);

        $statements = array();

        foreach ($this->normalizeClauses($clauses) as $clause) {
            $statements[] =
                $this->useStatementFactory->createStatement(array($clause));
        }

        foreach ($clausesByType as $type => $typeClauses) {
            foreach ($this->normalizeClauses($typeClauses) as $clause) {
                $statements[] = $this->useStatementFactory
                    ->createStatement(array($clause), $type);
            }
        }

        return $statements;
    }

    private function normalizeClauses($clauses)
    {
        $normalized = array();
        $seen = array();

        foreach ($clauses as $clause) {
            $key = strval($clause);

            if (array_key_exists($key, $seen)) {
                continue;
            }

            $seen[$key] = true;
            $normalized[] = $clause;
        }

        sort($normalized, SORT_STRING);

        return $normalized;
    }

    private static $instance;
    private $useStatementFactory;
}
