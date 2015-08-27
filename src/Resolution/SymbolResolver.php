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
use InvalidArgumentException;

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
            self::$instance = new self(
                'function_exists',
                'defined',
                SymbolFactory::instance()
            );
        }

        return self::$instance;
    }

    /**
     * Construct a new symbol resolver.
     *
     * @param callable               $functionResolver The callback to use when determining if a function exists.
     * @param callable               $constantResolver The callback to use when determining if a constant exists.
     * @param SymbolFactoryInterface $symbolFactory    The symbol factory to use.
     */
    public function __construct(
        $functionResolver,
        $constantResolver,
        SymbolFactoryInterface $symbolFactory
    ) {
        $this->functionResolver = $functionResolver;
        $this->constantResolver = $constantResolver;
        $this->symbolFactory = $symbolFactory;
    }

    /**
     * Resolve a symbol against a resolution context.
     *
     * Symbols that are already qualified will be returned unaltered.
     *
     * @param ResolutionContextInterface $context The resolution context.
     * @param SymbolInterface            $symbol  The symbol to resolve.
     * @param string|null                $type    The symbol type.
     *
     * @return SymbolInterface The resolved, qualified symbol.
     */
    public function resolve(
        ResolutionContextInterface $context,
        SymbolInterface $symbol,
        $type = null
    ) {
        if ($symbol->isQualified()) {
            return $symbol;
        }

        $atoms = $symbol->atoms();
        $numAtoms = \count($atoms);
        $firstAtom = $atoms[0];

        if ('namespace' === $firstAtom) {
            $parent = $context->primaryNamespace();
        } else {
            $parent = $context->symbolByAtom($firstAtom, $type);
        }

        if ($parent) {
            $symbol = $this->symbolFactory->createFromAtoms(
                \array_merge($parent->atoms(), \array_slice($atoms, 1)),
                true
            );
        } else {
            $symbol = $this->symbolFactory->createFromAtoms(
                \array_merge($context->primaryNamespace()->atoms(), $atoms),
                true
            );
        }

        if (null === $type) {
            return $symbol;
        }

        $atoms = $symbol->atoms();
        $numAtoms = \count($atoms);

        if ('const' === $type) {
            $callback = $this->constantResolver;
        } elseif ('function' === $type) {
            $callback = $this->functionResolver;
        } else {
            throw new InvalidArgumentException(
                \sprintf(
                    'Unsupported symbol type %s.',
                    \var_export($type, true)
                )
            );
        }

        if (!$callback(\strval($symbol))) {
            return $this->symbolFactory
                ->createFromAtoms(array($atoms[$numAtoms - 1]));
        }

        return $symbol;
    }

    private static $instance;
    private $functionResolver;
    private $constantResolver;
    private $symbolFactory;
}
