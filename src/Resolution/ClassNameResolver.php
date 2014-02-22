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

use Eloquent\Cosmos\ClassName\ClassNameInterface;
use Eloquent\Cosmos\ClassName\ClassNameReference;
use Eloquent\Cosmos\ClassName\QualifiedClassNameInterface;
use Eloquent\Pathogen\AbsolutePathInterface;
use Eloquent\Pathogen\PathInterface;

/**
 * Resolves class names and references into qualified class names.
 */
class ClassNameResolver implements ClassNameResolverInterface
{
    /**
     * Get a static instance of this resolver.
     *
     * @return ClassNameResolverInterface The static resolver.
     */
    public static function instance()
    {
        if (null === static::$instance) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    /**
     * Construct a new class name resolver.
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
     * Resolve a class name against the supplied namespace.
     *
     * This method assumes no use statements are defined.
     *
     * @param AbsolutePathInterface $primaryNamespace The namespace.
     * @param PathInterface         $className        The class name to resolve.
     *
     * @return QualifiedClassNameInterface The resolved, qualified class name.
     */
    // @codeCoverageIgnoreStart
    public function resolve(
        // @codeCoverageIgnoreEnd
        AbsolutePathInterface $primaryNamespace,
        PathInterface $className
    ) {
        return $this->resolveAgainstContext(
            $this->contextFactory()->create($primaryNamespace),
            $className
        );
    }

    /**
     * Resolve a class name against the supplied resolution context.
     *
     * Class names that are already qualified will be returned unaltered.
     *
     * @param ResolutionContextInterface $context   The resolution context.
     * @param ClassNameInterface         $className The class name to resolve.
     *
     * @return QualifiedClassNameInterface The resolved, qualified class name.
     */
    public function resolveAgainstContext(
        ResolutionContextInterface $context,
        ClassNameInterface $className
    ) {
        if ($className instanceof QualifiedClassNameInterface) {
            return $className;
        }

        if ($firstAtom = $className->firstAtomShortName()) {
            if (ClassNameReference::NAMESPACE_ATOM === $firstAtom->atomAt(0)) {
                $parent = $context->primaryNamespace();
            } else {
                $parent = $context->classNameByShortName($firstAtom);
            }

            if ($parent) {
                if (count($className->atoms()) < 2) {
                    return $parent;
                }

                return $parent->joinAtomSequence($className->sliceAtoms(1));
            }
        }

        return $context->primaryNamespace()->join($className);
    }

    /**
     * Find the shortest class name that will resolve to the supplied qualified
     * class name from within the supplied resolution context.
     *
     * If the qualified class is not a child of the primary namespace, and there
     * are no related use statements, this method will return a qualified class
     * name.
     *
     * @param ResolutionContextInterface  $context   The resolution context.
     * @param QualifiedClassNameInterface $className The class name to resolve.
     *
     * @return ClassNameInterface The shortest class name.
     */
    public function relativeToContext(
        ResolutionContextInterface $context,
        QualifiedClassNameInterface $className
    ) {
        $className = $className->normalize();

        if ($context->primaryNamespace()->isAncestorOf($className)) {
            $match = $className->relativeTo($context->primaryNamespace());

            if ($context->classNameByShortName($match->firstAtomShortName())) {
                $match = $match
                    ->replace(0, array(ClassNameReference::NAMESPACE_ATOM), 0);
            }
        } else {
            $match = $className;
        }

        $matchSize = count($match->atoms());

        foreach ($context->useStatements() as $useStatement) {
            if ($useStatement->className()->atoms() === $className->atoms()) {
                $match = $useStatement->effectiveAlias();
                $matchSize = 1;
            } elseif ($useStatement->className()->isAncestorOf($className)) {
                $thisMatch = $useStatement->effectiveAlias()
                    ->join($className->relativeTo($useStatement->className()));
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
