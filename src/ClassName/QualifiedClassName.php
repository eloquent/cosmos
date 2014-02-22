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

use Eloquent\Cosmos\Resolution\ClassNameResolver;
use Eloquent\Cosmos\Resolution\ClassNameResolverInterface;
use Eloquent\Pathogen\AbsolutePath;

/**
 * Represents a fully qualified class name.
 */
class QualifiedClassName extends AbsolutePath implements
    QualifiedClassNameInterface
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
     * Construct a new fully qualified class name.
     *
     * @param mixed<string> $atoms The class name atoms.
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

        return $this->createPath(array($atoms[0]), false);
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
            throw new Exception\InvalidClassNameAtomException($atom);
        }
    }

    /**
     * Get the class name factory.
     *
     * @return Factory\ClassNameFactoryInterface The class name factory.
     */
    protected static function factory()
    {
        return Factory\ClassNameFactory::instance();
    }

    /**
     * Get the class name normalizer.
     *
     * @return Normalizer\ClassNameNormalizerInterface The class name normalizer.
     */
    protected static function normalizer()
    {
        return Normalizer\ClassNameNormalizer::instance();
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
