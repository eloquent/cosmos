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

use Eloquent\Cosmos\Resolution\Context\Factory\ResolutionContextFactory;
use Eloquent\Cosmos\Resolution\Context\Factory\ResolutionContextFactoryInterface;
use Eloquent\Cosmos\Resolution\Context\ResolutionContextInterface;
use Eloquent\Cosmos\Symbol\QualifiedSymbolInterface;
use Eloquent\Cosmos\Symbol\SymbolInterface;
use Eloquent\Cosmos\Symbol\SymbolReference;
use Eloquent\Cosmos\Symbol\SymbolType;
use Eloquent\Cosmos\UseStatement\UseStatementType;
use Eloquent\Pathogen\AbsolutePathInterface;
use Eloquent\Pathogen\PathInterface;

/**
 * Resolves symbols into qualified symbols.
 */
class SymbolResolver implements SymbolResolverInterface
{
    /**
     * Get a static instance of this resolver.
     *
     * @return SymbolResolverInterface The static resolver.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Construct a new symbol resolver.
     *
     * @param callable|null                          $functionResolver The callback to use when determining if a function exists.
     * @param callable|null                          $constantResolver The callback to use when determining if a constant exists.
     * @param ResolutionContextFactoryInterface|null $contextFactory   The resolution context factory to use.
     */
    public function __construct(
        $functionResolver = null,
        $constantResolver = null,
        ResolutionContextFactoryInterface $contextFactory = null
    ) {
        if (null === $functionResolver) {
            $functionResolver = 'function_exists';
        }
        if (null === $constantResolver) {
            $constantResolver = 'defined';
        }
        if (null === $contextFactory) {
            $contextFactory = ResolutionContextFactory::instance();
        }

        $this->functionResolver = $functionResolver;
        $this->constantResolver = $constantResolver;
        $this->contextFactory = $contextFactory;
    }

    /**
     * Get the function resolver.
     *
     * @return callable The function resolver.
     */
    public function functionResolver()
    {
        return $this->functionResolver;
    }

    /**
     * Get the constant resolver.
     *
     * @return callable The constant resolver.
     */
    public function constantResolver()
    {
        return $this->constantResolver;
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
     * Resolve a symbol against the supplied namespace.
     *
     * This method assumes no use statements are defined.
     *
     * @param AbsolutePathInterface $primaryNamespace The namespace.
     * @param PathInterface         $symbol           The symbol to resolve.
     *
     * @return QualifiedSymbolInterface The resolved, qualified symbol.
     */
    public function resolve(
        AbsolutePathInterface $primaryNamespace,
        PathInterface $symbol
    ) {
        return $this
            ->resolveAsType($primaryNamespace, $symbol, SymbolType::CLA55());
    }

    /**
     * Resolve a symbol of a specified type against the supplied namespace.
     *
     * This method assumes no use statements are defined.
     *
     * @param QualifiedSymbolInterface $primaryNamespace The namespace.
     * @param SymbolInterface          $symbol           The symbol to resolve.
     * @param SymbolType               $type             The symbol type.
     *
     * @return QualifiedSymbolInterface The resolved, qualified symbol.
     */
    public function resolveAsType(
        QualifiedSymbolInterface $primaryNamespace,
        SymbolInterface $symbol,
        SymbolType $type
    ) {
        return $this->handleFallback(
            $this->resolveAgainstContext(
                $this->contextFactory()->create($primaryNamespace),
                $symbol
            ),
            $type
        );
    }

    /**
     * Resolve a symbol against the supplied resolution context.
     *
     * Symbols that are already qualified will be returned unaltered.
     *
     * @param ResolutionContextInterface $context The resolution context.
     * @param SymbolInterface            $symbol  The symbol to resolve.
     * @param SymbolType|null            $type    The symbol type.
     *
     * @return QualifiedSymbolInterface The resolved, qualified symbol.
     */
    public function resolveAgainstContext(
        ResolutionContextInterface $context,
        SymbolInterface $symbol,
        SymbolType $type = null
    ) {
        if ($symbol instanceof AbsolutePathInterface) {
            return $symbol;
        }

        $numAtoms = count($symbol->atoms());

        if ($numAtoms > 1 || null === $type) {
            $type = SymbolType::CLA55();
        }

        if (SymbolReference::NAMESPACE_ATOM === $symbol->atomAt(0)) {
            $parent = $context->primaryNamespace();
        } else {
            $parent = $context
                ->symbolByFirstAtom($symbol->firstAtomAsReference(), $type);
        }

        if ($parent) {
            if ($numAtoms < 2) {
                $symbol = $parent;
            } else {
                $symbol = $parent->joinAtomSequence($symbol->sliceAtoms(1));
            }
        } else {
            $symbol = $context->primaryNamespace()->join($symbol);
        }

        return $this->handleFallback($symbol, $type);
    }

    /**
     * Find the shortest symbol that will resolve to the supplied qualified
     * symbol from within the supplied resolution context.
     *
     * If the qualified symbol is not a child of the primary namespace, and
     * there are no related use statements, this method will return a qualified
     * symbol.
     *
     * @param ResolutionContextInterface $context The resolution context.
     * @param QualifiedSymbolInterface   $symbol  The symbol to resolve.
     * @param SymbolType|null            $type    The symbol type.
     *
     * @return SymbolInterface The shortest symbol.
     */
    public function relativeToContext(
        ResolutionContextInterface $context,
        QualifiedSymbolInterface $symbol,
        SymbolType $type = null
    ) {
        if (null === $type) {
            $type = SymbolType::CLA55();
        }

        $symbol = $symbol->normalize();

        if ($context->primaryNamespace()->isAncestorOf($symbol)) {
            $match = $symbol->relativeTo($context->primaryNamespace());

            if (
                $context
                    ->symbolByFirstAtom($match->firstAtomAsReference(), $type)
            ) {
                $match = $match
                    ->replace(0, array(SymbolReference::NAMESPACE_ATOM), 0);
            }
        } else {
            $match = $symbol;
        }

        $matchSize = count($match->atoms());
        $useStatements = $context
            ->useStatementsByType(UseStatementType::memberBySymbolType($type));

        foreach ($useStatements as $useStatement) {
            foreach ($useStatement->clauses() as $clause) {
                if ($clause->symbol()->atoms() === $symbol->atoms()) {
                    $match = $clause->effectiveAlias();
                    $matchSize = 1;
                } elseif ($clause->symbol()->isAncestorOf($symbol)) {
                    $thisMatch = $clause->effectiveAlias()
                        ->join($symbol->relativeTo($clause->symbol()));
                    $thisMatchSize = count($thisMatch->atoms());

                    if ($thisMatchSize < $matchSize) {
                        $match = $thisMatch;
                        $matchSize = $thisMatchSize;
                    }
                }
            }
        }

        return $match;
    }

    private function handleFallback(
        QualifiedSymbolInterface $symbol,
        SymbolType $type
    ) {
        if ($type->isType()) {
            return $symbol;
        }

        $numAtoms = count($symbol->atoms());

        if ($numAtoms < 2) {
            return $symbol;
        }

        if (SymbolType::CONSTANT() === $type) {
            $callback = $this->constantResolver();
        } else {
            $callback = $this->functionResolver();
        }

        if (!$callback($symbol->string())) {
            return $symbol->replace(0, array(), $numAtoms - 1);
        }

        return $symbol;
    }

    private static $instance;
    private $functionResolver;
    private $constantResolver;
    private $contextFactory;
}
