<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution;

use Eloquent\Cosmos\Resolution\Context\ResolutionContextInterface;
use Eloquent\Cosmos\Symbol\SymbolFactory;
use Eloquent\Cosmos\Symbol\SymbolFactoryInterface;
use Eloquent\Cosmos\Symbol\SymbolInterface;

/**
 * Generates references to qualified symbols.
 *
 * @api
 */
class SymbolReferenceGenerator implements SymbolReferenceGeneratorInterface
{
    /**
     * Get a static instance of this generator.
     *
     * @return SymbolReferenceGeneratorInterface The static generator.
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self(SymbolFactory::instance());
        }

        return self::$instance;
    }

    /**
     * Construct a new symbol resolver.
     *
     * @param SymbolFactoryInterface $symbolFactory The symbol factory to use.
     */
    public function __construct(SymbolFactoryInterface $symbolFactory)
    {
        $this->symbolFactory = $symbolFactory;
    }

    /**
     * Find the shortest symbol that will resolve to the supplied qualified
     * symbol from within the supplied resolution context.
     *
     * If the qualified symbol is not a child of the primary namespace, and
     * there are no related use statements, this method will return a qualified
     * symbol.
     *
     * @api
     *
     * @param ResolutionContextInterface $context The resolution context.
     * @param SymbolInterface            $symbol  The symbol to resolve.
     * @param string|null                $type    The symbol type.
     *
     * @return SymbolInterface The shortest symbol.
     */
    public function referenceTo(
        ResolutionContextInterface $context,
        SymbolInterface $symbol,
        $type = null
    ) {
        $atoms = $symbol->atoms();
        $namespace = $context->primaryNamespace();
        $namespaceAtoms = $namespace->atoms();
        $numNamespaceAtoms = \count($namespaceAtoms);

        $isDescendant =
            $namespaceAtoms === \array_slice($atoms, 0, $numNamespaceAtoms);

        if ($isDescendant) {
            // symbol is descendant of namespace
            $match = \array_slice($atoms, $numNamespaceAtoms);

            if (!$match) {
                // symbol is exactly equal to namespace
                return $symbol;
            }

            if ($context->symbolByAtom($match[0], $type)) {
                // use statment clashes, use namespace keyword
                \array_unshift($match, 'namespace');
            }
        } else {
            $match = $atoms;
        }

        $matchSize = \count($match);

        foreach ($context->useStatementsByType($type) as $useStatement) {
            if ($matchSize < 2) {
                break;
            }

            foreach ($useStatement->clauses() as $clause) {
                $clauseAtoms = $clause->symbol()->atoms();
                $numClauseAtoms = \count($clauseAtoms);
                $isDescendant =
                    $clauseAtoms === \array_slice($atoms, 0, $numClauseAtoms);

                if ($isDescendant) {
                    // symbol is descendant of use statement
                    $thisMatch = \array_slice($atoms, $numClauseAtoms);
                    \array_unshift($thisMatch, $clause->effectiveAlias());
                    $thisMatchSize = \count($thisMatch);

                    if ($thisMatchSize < $matchSize) {
                        // this match is better
                        $match = $thisMatch;
                        $matchSize = $thisMatchSize;
                    }
                }
            }
        }

        if ($matchSize >= count($atoms)) {
            return $symbol;
        }

        return $this->symbolFactory->createFromAtoms($match, false);
    }

    private static $instance;
    private $symbolFactory;
}
