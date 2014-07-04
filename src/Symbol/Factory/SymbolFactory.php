<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Symbol\Factory;

use Eloquent\Cosmos\Symbol\Exception\InvalidSymbolAtomException;
use Eloquent\Cosmos\Symbol\QualifiedSymbol;
use Eloquent\Cosmos\Symbol\QualifiedSymbolInterface;
use Eloquent\Cosmos\Symbol\SymbolInterface;
use Eloquent\Cosmos\Symbol\SymbolReference;
use Eloquent\Pathogen\Exception\InvalidPathAtomExceptionInterface;
use ReflectionClass;
use ReflectionFunction;

/**
 * Creates symbol instances.
 */
class SymbolFactory implements SymbolFactoryInterface
{
    /**
     * The regular expression used to validate symbol atoms.
     */
    const ATOM_PATTERN = '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/S';

    /**
     * Get a static instance of this factory.
     *
     * @return SymbolFactoryInterface The static factory.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Creates a new symbol from its string representation.
     *
     * @param string $symbol The string representation of the symbol.
     *
     * @return SymbolInterface            The newly created symbol.
     * @throws InvalidSymbolAtomException If an invalid symbol atom is supplied.
     */
    public function create($symbol)
    {
        if ('' === $symbol) {
            $symbol = QualifiedSymbol::SELF_ATOM;
        }

        if (QualifiedSymbol::ATOM_SEPARATOR === $symbol) {
            return $this->globalNamespace();
        }

        $isQualified = false;

        $atoms = explode(QualifiedSymbol::ATOM_SEPARATOR, $symbol);
        $numAtoms = count($atoms);

        if ($numAtoms > 1) {
            if ('' === $atoms[0]) {
                $isQualified = true;
                array_shift($atoms);
                --$numAtoms;
            }

            if ('' === $atoms[$numAtoms - 1]) {
                array_pop($atoms);
                --$numAtoms;
            }
        }

        foreach ($atoms as $index => $atom) {
            if ('.' === $atom || '..' === $atom) {
                continue;
            }

            if ('' === $atom) {
                array_splice($atoms, $index, 1);
                --$numAtoms;
            } elseif (!preg_match(static::ATOM_PATTERN, $atom)) {
                throw new InvalidSymbolAtomException($atom);
            }
        }

        if ($isQualified) {
            return QualifiedSymbol::constructSymbolUnsafe($atoms);
        }

        return SymbolReference::constructSymbolUnsafe($atoms);
    }

    /**
     * Creates a new symbol from a set of symbol atoms.
     *
     * Unless otherwise specified, created symbols will be qualified.
     *
     * @param mixed<string> $atoms                The symbol atoms.
     * @param boolean|null  $isQualified          True if the symbol is fully qualified.
     * @param boolean|null  $hasTrailingSeparator Ignored.
     *
     * @return SymbolInterface                   The newly created symbol.
     * @throws InvalidPathAtomExceptionInterface If any of the supplied atoms are invalid.
     */
    public function createFromAtoms(
        $atoms,
        $isQualified = null,
        $hasTrailingSeparator = null
    ) {
        if (null === $isQualified) {
            $isQualified = true;
        }

        if ($isQualified) {
            return QualifiedSymbol::constructSymbol($atoms);
        }

        return SymbolReference::constructSymbol($atoms);
    }

    /**
     * Creates a new qualified symbol from its string representation, regardless
     * of whether it starts with a namespace separator.
     *
     * This method emulates the manner in which symbols are typically
     * interpreted at run time.
     *
     * @param string $symbol The string representation of the symbol.
     *
     * @return QualifiedSymbolInterface The newly created qualified symbol instance.
     */
    public function createRuntime($symbol)
    {
        if (QualifiedSymbol::ATOM_SEPARATOR !== substr($symbol, 0, 1)) {
            $symbol = QualifiedSymbol::ATOM_SEPARATOR . $symbol;
        }

        return $this->create($symbol);
    }

    /**
     * Get the class name of the supplied object.
     *
     * @param object $object The object.
     *
     * @return QualifiedSymbolInterface The object's qualified class name.
     */
    public function createFromObject($object)
    {
        return $this->createRuntime(get_class($object));
    }

    /**
     * Get the class name of the supplied class or object reflector.
     *
     * @param ReflectionClass $class The class or object reflector.
     *
     * @return QualifiedSymbolInterface The qualified class name.
     */
    public function createFromClass(ReflectionClass $class)
    {
        return $this->createRuntime($class->getName());
    }

    /**
     * Get the function name of the supplied function reflector.
     *
     * @param ReflectionFunction $function The function reflector.
     *
     * @return QualifiedSymbolInterface The qualified function name.
     */
    public function createFromFunction(ReflectionFunction $function)
    {
        return $this->createRuntime($function->getName());
    }

    /**
     * Get the qualified symbol representing the global namespace.
     *
     * @return QualifiedSymbolInterface The global namespace symbol.
     */
    public function globalNamespace()
    {
        if (null === $this->globalNamespace) {
            $this->globalNamespace = $this->createFromAtoms(array(), true);
        }

        return $this->globalNamespace;
    }

    private static $instance;
    private $globalNamespace;
}
