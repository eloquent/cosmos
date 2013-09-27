<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Cosmos\ClassName;

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
     * Create a new class name instance.
     *
     * @param mixed<string> $atoms                The class name atoms.
     * @param boolean       $isQualified          True if the class name is fully qualified.
     * @param boolean|null  $hasTrailingSeparator Ignored.
     *
     * @return ClassNameInterface                 The newly created class name instance.
     * @throws InvalidPathAtomExceptionInterface  If any of the supplied atoms are invalid.
     */
    protected function createPath(
        $atoms,
        $isQualified,
        $hasTrailingSeparator = null
    ) {
        return new ClassNameReference($atoms);
    }
}
