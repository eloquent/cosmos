<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Symbol;

use Eloquent\Cosmos\Resolution\Context\ResolutionContextInterface;
use Eloquent\Cosmos\Resolution\Context\ResolutionContextVisitorInterface;
use Eloquent\Cosmos\Resolution\SymbolResolver;
use Eloquent\Cosmos\Resolution\SymbolResolverInterface;
use Eloquent\Cosmos\Symbol\Exception\InvalidSymbolAtomException;
use Eloquent\Cosmos\Symbol\Factory\SymbolFactory;
use Eloquent\Cosmos\Symbol\Factory\SymbolFactoryInterface;
use Eloquent\Cosmos\Symbol\Normalizer\SymbolNormalizer;
use Eloquent\Cosmos\Symbol\Normalizer\SymbolNormalizerInterface;
use Eloquent\Pathogen\Exception\EmptyPathAtomException;
use Eloquent\Pathogen\Exception\InvalidPathAtomExceptionInterface;
use Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException;
use Eloquent\Pathogen\RelativePath;

/**
 * Represents a symbol reference.
 */
class SymbolReference extends RelativePath implements SymbolReferenceInterface
{
    /**
     * The character used to separate symbol atoms.
     */
    const ATOM_SEPARATOR = '\\';

    /**
     * The character used to separate PEAR-style namespaces.
     */
    const EXTENSION_SEPARATOR = '_';

    /**
     * The regular expression used to validate symbol atoms.
     */
    const ATOM_PATTERN = '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/S';

    /**
     * The atom used to represent the current namespace.
     */
    const NAMESPACE_ATOM = 'namespace';

    /**
     * Construct a new symbol reference (internal use only).
     *
     * @internal This method is not intended for public use.
     *
     * @param mixed<string> $atoms The symbol atoms.
     *
     * @throws InvalidPathAtomExceptionInterface If any of the supplied symbol atoms are invalid.
     */
    public static function constructSymbol($atoms)
    {
        return new static(static::normalizeAtoms($atoms));
    }

    /**
     * Construct a new symbol reference (internal use only).
     *
     * @internal This method is not intended for public use.
     *
     * @param mixed<string> $atoms The symbol atoms.
     *
     * @throws InvalidPathAtomExceptionInterface If any of the supplied symbol atoms are invalid.
     */
    public static function constructSymbolUnsafe($atoms)
    {
        return new static($atoms);
    }

    /**
     * Get the first atom of this symbol as a symbol reference.
     *
     * If this symbol is already a short symbol reference, it will be returned
     * unaltered.
     *
     * @return SymbolReferenceInterface The short symbol.
     */
    public function firstAtomAsReference()
    {
        $atoms = $this->atoms();
        $numAtoms = count($atoms);
        if ($numAtoms < 2) {
            return $this;
        }

        return $this->createPath(array($atoms[0]), false);
    }

    /**
     * Get the last atom of this symbol as a symbol reference.
     *
     * If this symbol is already a short symbol reference, it will be returned
     * unaltered.
     *
     * @return SymbolReferenceInterface The short symbol.
     */
    public function lastAtomAsReference()
    {
        $atoms = $this->atoms();
        $numAtoms = count($atoms);
        if ($numAtoms < 2) {
            return $this;
        }

        return $this->createPath(array($atoms[$numAtoms - 1]), false);
    }

    /**
     * Resolve this symbol against the supplied resolution context.
     *
     * @param ResolutionContextInterface $context The resolution context.
     * @param SymbolType|null            $type    The symbol type.
     *
     * @return QualifiedSymbolInterface The resolved, qualified symbol.
     */
    public function resolveAgainstContext(
        ResolutionContextInterface $context,
        SymbolType $type = null
    ) {
        return static::resolver()
            ->resolveAgainstContext($context, $this, $type);
    }

    /**
     * Accept a visitor.
     *
     * @param ResolutionContextVisitorInterface $visitor The visitor to accept.
     *
     * @return mixed The result of visitation.
     */
    public function accept(ResolutionContextVisitorInterface $visitor)
    {
        return $visitor->visitSymbolReference($this);
    }

    /**
     * Normalizes and validates a sequence of symbol atoms.
     *
     * This method is called internally by the constructor upon instantiation.
     * It can be overridden in child classes to change how symbol atoms are
     * normalized and/or validated.
     *
     * @param mixed<string> $atoms The symbol atoms to normalize.
     *
     * @return array<string>                      The normalized symbol atoms.
     * @throws EmptyPathAtomException             If any symbol atom is empty.
     * @throws PathAtomContainsSeparatorException If any symbol atom contains a separator.
     */
    protected static function normalizeAtoms($atoms)
    {
        foreach ($atoms as $atom) {
            if (static::SELF_ATOM === $atom || static::PARENT_ATOM === $atom) {
                continue;
            }

            if ('' === $atom) {
                throw new EmptyPathAtomException;
            } elseif (false !== strpos($atom, static::ATOM_SEPARATOR)) {
                throw new PathAtomContainsSeparatorException($atom);
            } elseif (!preg_match(static::ATOM_PATTERN, $atom)) {
                throw new InvalidSymbolAtomException($atom);
            }
        }

        return $atoms;
    }

    /**
     * Construct a new symbol reference (internal use only).
     *
     * @internal This method is not intended for public use.
     *
     * @param mixed<string> $atoms The symbol atoms.
     *
     * @throws InvalidPathAtomExceptionInterface If any of the supplied symbol atoms are invalid.
     */
    protected function __construct($atoms)
    {
        parent::__construct($atoms);
    }

    /**
     * Creates a new path instance of the most appropriate type.
     *
     * This method is called internally every time a new path instance is
     * created as part of another method call. It can be overridden in child
     * classes to change which classes are used when creating new path
     * instances.
     *
     * @param mixed<string> $atoms                The path atoms.
     * @param boolean       $isAbsolute           True if the new path should be absolute.
     * @param boolean|null  $hasTrailingSeparator True if the new path should have a trailing separator.
     *
     * @return PathInterface The newly created path instance.
     */
    protected function createPath(
        $atoms,
        $isAbsolute,
        $hasTrailingSeparator = null
    ) {
        if ($isAbsolute) {
            return QualifiedSymbol::constructSymbolUnsafe(
                $atoms,
                $hasTrailingSeparator
            );
        }

        return SymbolReference::constructSymbolUnsafe(
            $atoms,
            $hasTrailingSeparator
        );
    }

    /**
     * Get the symbol factory.
     *
     * @return SymbolFactoryInterface The symbol factory.
     */
    protected static function factory()
    {
        return SymbolFactory::instance();
    }

    /**
     * Get the symbol normalizer.
     *
     * @return SymbolNormalizerInterface The symbol normalizer.
     */
    protected static function normalizer()
    {
        return SymbolNormalizer::instance();
    }

    /**
     * Get the symbol resolver.
     *
     * @return SymbolResolverInterface The symbol resolver.
     */
    protected static function resolver()
    {
        return SymbolResolver::instance();
    }
}
