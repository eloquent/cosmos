<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\ClassName;

use Eloquent\Cosmos\ClassName\Exception\InvalidClassNameAtomException;
use Eloquent\Cosmos\ClassName\Factory\ClassNameFactory;
use Eloquent\Cosmos\ClassName\Factory\ClassNameFactoryInterface;
use Eloquent\Cosmos\ClassName\Normalizer\ClassNameNormalizer;
use Eloquent\Cosmos\ClassName\Normalizer\ClassNameNormalizerInterface;
use Eloquent\Cosmos\Resolution\ClassNameResolver;
use Eloquent\Cosmos\Resolution\ClassNameResolverInterface;
use Eloquent\Cosmos\Resolution\Context\ResolutionContextInterface;
use Eloquent\Pathogen\Exception\InvalidPathAtomExceptionInterface;
use Eloquent\Pathogen\RelativePath;

/**
 * Represents a class name reference.
 */
class ClassNameReference extends RelativePath implements
    ClassNameReferenceInterface
{
    /**
     * The character used to separate class name atoms.
     */
    const ATOM_SEPARATOR = '\\';

    /**
     * The character used to separate PEAR-style namespaces.
     */
    const EXTENSION_SEPARATOR = '_';

    /**
     * The regular expression used to validate class name atoms.
     */
    const CLASS_NAME_PATTERN = '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/';

    /**
     * The atom used to represent the current namespace.
     */
    const NAMESPACE_ATOM = 'namespace';

    /**
     * Construct a new class name reference.
     *
     * @param mixed<string> $atoms The class name atoms.
     *
     * @throws InvalidPathAtomExceptionInterface If any of the supplied class name atoms are invalid.
     */
    public function __construct($atoms)
    {
        parent::__construct($atoms);
    }

    /**
     * Get the last atom of this class name as a class name reference.
     *
     * If this class name is already a short class name reference, it will be
     * returned unaltered.
     *
     * @return ClassNameReferenceInterface The short class name.
     */
    public function shortName()
    {
        $atoms = $this->atoms();
        $numAtoms = count($atoms);
        if ($numAtoms < 2) {
            return $this;
        }

        return $this->createPath(array($atoms[$numAtoms - 1]), false);
    }

    /**
     * Get the first atom of this class name as a class name reference.
     *
     * If this class name is already a short class name reference, it will be
     * returned unaltered.
     *
     * @return ClassNameReferenceInterface The short class name.
     */
    public function firstAtomShortName()
    {
        $atoms = $this->atoms();
        $numAtoms = count($atoms);
        if ($numAtoms < 2) {
            return $this;
        }

        return $this->createPath(array($atoms[0]), false);
    }

    /**
     * Resolve this class name against the supplied resolution context.
     *
     * @param ResolutionContextInterface $context The resolution context.
     *
     * @return QualifiedClassNameInterface The resolved, qualified class name.
     */
    public function resolveAgainstContext(ResolutionContextInterface $context)
    {
        return static::resolver()->resolveAgainstContext($context, $this);
    }

    /**
     * Validates the supplied class name atom.
     *
     * @param string $atom The atom to validate.
     */
    protected function validateAtom($atom)
    {
        if (static::SELF_ATOM === $atom || static::PARENT_ATOM === $atom) {
            return;
        }

        if (!preg_match(static::CLASS_NAME_PATTERN, $atom)) {
            throw new InvalidClassNameAtomException($atom);
        }
    }

    /**
     * Get the class name factory.
     *
     * @return ClassNameFactoryInterface The class name factory.
     */
    protected static function factory()
    {
        return ClassNameFactory::instance();
    }

    /**
     * Get the class name normalizer.
     *
     * @return ClassNameNormalizerInterface The class name normalizer.
     */
    protected static function normalizer()
    {
        return ClassNameNormalizer::instance();
    }

    /**
     * Get the class name resolver.
     *
     * @return ClassNameResolverInterface The class name resolver.
     */
    protected static function resolver()
    {
        return ClassNameResolver::instance();
    }
}
