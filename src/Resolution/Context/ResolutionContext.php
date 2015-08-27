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
     * @return SymbolInterface|null The namespace, or null if global.
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
     * Get the use statements by type.
     *
     * @param string $type The type.
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
     * Get the symbol associated with the supplied symbol reference's first
     * atom.
     *
     * @param SymbolInterface $symbol The symbol reference.
     * @param string|null     $type   The symbol type.
     *
     * @return SymbolInterface|null The symbol, or null if no associated symbol exists.
     */
    public function symbolByFirstAtom(SymbolInterface $symbol, $type = null)
    {
        $firstAtom = $symbol->firstAtom();

        if (null === $type) {
            if (isset($this->symbolIndexNoType[$firstAtom])) {
                return $this->symbolIndexNoType[$firstAtom];
            }
        } else {
            if (isset($this->symbolIndexByType[$type][$firstAtom])) {
                return $this->symbolIndexByType[$type][$firstAtom];
            }
        }

        return null;
    }

    /**
     * Get the string representation of this context.
     *
     * @return string The string representation.
     */
    public function __toString()
    {
        if (null === $this->primaryNamespace) {
            return implode(";\n", $this->useStatements) . ";\n";
        }

        return 'namespace ' .
            $this->primaryNamespace->runtimeString() .
            ";\n\n" .
            implode(";\n", $this->useStatements) . ";\n";
    }

    private $primaryNamespace;
    private $useStatements;
    private $useStatementsNoType;
    private $useStatementsByType;
    private $symbolIndexNoType;
    private $symbolIndexByType;
}
