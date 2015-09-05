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
use Eloquent\Cosmos\UseStatement\UseStatementInterface;

/**
 * Represents a combined namespace and set of use statements.
 *
 * @api
 */
class ResolutionContext implements ResolutionContextInterface
{
    /**
     * Construct a new symbol resolution context.
     *
     * @param SymbolInterface|null         $primaryNamespace The namespace, or null for the global namespace.
     * @param array<UseStatementInterface> $useStatements    The use statements.
     */
    public function __construct(
        SymbolInterface $primaryNamespace = null,
        array $useStatements
    ) {
        $this->primaryNamespace = $primaryNamespace;
        $this->useStatements = $useStatements;

        $this->useStatementsNoType = array();
        $this->useStatementsByType = array();
        $this->symbolIndexNoType = array();
        $this->symbolIndexByType = array();

        foreach ($useStatements as $useStatement) {
            $type = $useStatement->type();

            if (null === $type) {
                $this->useStatementsNoType[] = $useStatement;

                foreach ($useStatement->clauses() as $clause) {
                    $this->symbolIndexNoType[$clause->effectiveAlias()] =
                        $clause->symbol();
                }
            } else {
                if (!isset($this->useStatementsByType[$type])) {
                    $this->useStatementsByType[$type] = array();
                }
                if (!isset($this->symbolIndexByType[$type])) {
                    $this->symbolIndexByType[$type] = array();
                }

                $this->useStatementsByType[$type][] = $useStatement;

                foreach ($useStatement->clauses() as $clause) {
                    $this->symbolIndexByType[$type][$clause->effectiveAlias()] =
                        $clause->symbol();
                }
            }
        }
    }

    /**
     * Get the namespace.
     *
     * @api
     *
     * @return SymbolInterface|null The namespace, or null if global.
     */
    public function primaryNamespace()
    {
        return $this->primaryNamespace;
    }

    /**
     * Get the use statements.
     *
     * @api
     *
     * @return array<UseStatementInterface> The use statements.
     */
    public function useStatements()
    {
        return $this->useStatements;
    }

    /**
     * Get the use statements by type.
     *
     * @api
     *
     * @param string|null $type The type.
     *
     * @return array<UseStatementInterface> The use statements.
     */
    public function useStatementsByType($type)
    {
        if (null === $type) {
            return $this->useStatementsNoType;
        }

        if (isset($this->useStatementsByType[$type])) {
            return $this->useStatementsByType[$type];
        }

        return array();
    }

    /**
     * Get the symbol associated with the supplied atom.
     *
     * @api
     *
     * @param string      $atom The atom.
     * @param string|null $type The symbol type.
     *
     * @return SymbolInterface|null The symbol, or null if no associated symbol exists.
     */
    public function symbolByAtom($atom, $type = null)
    {
        if (null === $type) {
            if (isset($this->symbolIndexNoType[$atom])) {
                return $this->symbolIndexNoType[$atom];
            }
        } else {
            if (isset($this->symbolIndexByType[$type][$atom])) {
                return $this->symbolIndexByType[$type][$atom];
            }
        }

        return null;
    }

    /**
     * Get the string representation of this context.
     *
     * @api
     *
     * @return string The string representation.
     */
    public function __toString()
    {
        if ($this->useStatements) {
            $statements = \implode(";\n", $this->useStatements) . ";\n";

            if (!$this->primaryNamespace) {
                return $statements;
            }

            return 'namespace ' .
                $this->primaryNamespace->runtimeString() .
                ";\n\n" .
                $statements;
        }

        if (!$this->primaryNamespace) {
            return '';
        }

        return 'namespace ' . $this->primaryNamespace->runtimeString() . ";\n";
    }

    private $primaryNamespace;
    private $useStatements;
    private $useStatementsNoType;
    private $useStatementsByType;
    private $symbolIndexNoType;
    private $symbolIndexByType;
}
