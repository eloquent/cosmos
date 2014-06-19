<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
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
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Construct a new symbol resolver.
     *
     * @param ResolutionContextFactoryInterface|null $contextFactory The resolution context factory to use.
     */
    public function __construct(
        ResolutionContextFactoryInterface $contextFactory = null
    ) {
        if (null === $contextFactory) {
            $contextFactory = ResolutionContextFactory::instance();
        }

        $this->contextFactory = $contextFactory;
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
        return $this->resolveAgainstContext(
            $this->contextFactory()->create($primaryNamespace),
            $symbol
        );
    }

    /**
     * Resolve a symbol against the supplied resolution context.
     *
     * Symbols that are already qualified will be returned unaltered.
     *
     * @param ResolutionContextInterface $context The resolution context.
     * @param SymbolInterface            $symbol  The symbol to resolve.
     *
     * @return QualifiedSymbolInterface The resolved, qualified symbol.
     */
    public function resolveAgainstContext(
        ResolutionContextInterface $context,
        SymbolInterface $symbol
    ) {
        if ($symbol instanceof AbsolutePathInterface) {
            return $symbol;
        }

        if (SymbolReference::NAMESPACE_ATOM === $symbol->atomAt(0)) {
            $parent = $context->primaryNamespace();
        } else {
            $parent = $context
                ->symbolByFirstAtom($symbol->firstAtomAsReference());
        }

        if ($parent) {
            if (count($symbol->atoms()) < 2) {
                return $parent;
            }

            return $parent->joinAtomSequence($symbol->sliceAtoms(1));
        }

        return $context->primaryNamespace()->join($symbol);
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
     *
     * @return SymbolInterface The shortest symbol.
     */
    public function relativeToContext(
        ResolutionContextInterface $context,
        QualifiedSymbolInterface $symbol
    ) {
        $symbol = $symbol->normalize();

        if ($context->primaryNamespace()->isAncestorOf($symbol)) {
            $match = $symbol->relativeTo($context->primaryNamespace());

            if ($context->symbolByFirstAtom($match->firstAtomAsReference())) {
                $match = $match
                    ->replace(0, array(SymbolReference::NAMESPACE_ATOM), 0);
            }
        } else {
            $match = $symbol;
        }

        $matchSize = count($match->atoms());

        foreach ($context->useStatements() as $useStatement) {
            if ($useStatement->symbol()->atoms() === $symbol->atoms()) {
                $match = $useStatement->effectiveAlias();
                $matchSize = 1;
            } elseif ($useStatement->symbol()->isAncestorOf($symbol)) {
                $thisMatch = $useStatement->effectiveAlias()
                    ->join($symbol->relativeTo($useStatement->symbol()));
                $thisMatchSize = count($thisMatch->atoms());

                if ($thisMatchSize < $matchSize) {
                    $match = $thisMatch;
                    $matchSize = $thisMatchSize;
                }
            }
        }

        return $match;
    }

    private static $instance;
    private $contextFactory;
}
