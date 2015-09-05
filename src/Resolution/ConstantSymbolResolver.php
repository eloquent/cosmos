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
 * Resolves constant symbols into qualified symbols.
 *
 * @api
 */
class ConstantSymbolResolver implements SymbolResolverInterface
{
    /**
     * Get a static instance of this resolver.
     *
     * @return SymbolResolverInterface The static resolver.
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self(SymbolFactory::instance());
        }

        return self::$instance;
    }

    /**
     * Construct a new constant symbol resolver.
     *
     * @param SymbolFactoryInterface $symbolFactory    The symbol factory to use.
     * @param callable               $constantResolver The callback to use when determining if a constant exists.
     */
    public function __construct(
        SymbolFactoryInterface $symbolFactory,
        $constantResolver = 'defined'
    ) {
        $this->symbolFactory = $symbolFactory;
        $this->constantResolver = $constantResolver;
    }

    /**
     * Resolve a symbol against a resolution context.
     *
     * @api
     *
     * @param ResolutionContextInterface $context The resolution context.
     * @param SymbolInterface            $symbol  The symbol to resolve.
     *
     * @return SymbolInterface The resolved, qualified symbol.
     */
    public function resolve(
        ResolutionContextInterface $context,
        SymbolInterface $symbol
    ) {
        if ($symbol->isQualified()) {
            return $symbol;
        }

        $atoms = $symbol->atoms();
        $hasMultipleAtoms = \count($atoms) > 1;

        if ($hasMultipleAtoms) {
            if ('namespace' === $atoms[0]) {
                if ($namespace = $context->primaryNamespace()) {
                    return $this->symbolFactory->createFromAtoms(
                        \array_merge(
                            $namespace->atoms(),
                            \array_slice($atoms, 1)
                        ),
                        true
                    );
                } else {
                    return $this->symbolFactory
                        ->createFromAtoms(\array_slice($atoms, 1), true);
                }
            }

            if ($parent = $context->symbolByAtom($atoms[0])) {
                return $this->symbolFactory->createFromAtoms(
                    \array_merge($parent->atoms(), \array_slice($atoms, 1)),
                    true
                );
            }

            if ($namespace = $context->primaryNamespace()) {
                return $this->symbolFactory->createFromAtoms(
                    \array_merge($namespace->atoms(), $atoms),
                    true
                );
            }

            return $this->symbolFactory->createFromAtoms($atoms, true);
        }

        if ($parent = $context->symbolByAtom($atoms[0], 'const')) {
            return $parent;
        }

        if ($namespace = $context->primaryNamespace()) {
            $callback = $this->constantResolver;
            $namespaceSymbolAtoms = \array_merge($namespace->atoms(), $atoms);

            if ($callback(\implode('\\', $namespaceSymbolAtoms))) {
                return $this->symbolFactory
                    ->createFromAtoms($namespaceSymbolAtoms, true);
            }
        }

        return $this->symbolFactory->createFromAtoms($atoms, true);
    }

    private static $instance;
    private $symbolFactory;
    private $constantResolver;
}
