<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Context\Factory;

use Eloquent\Cosmos\ClassName\Factory\ClassNameFactory;
use Eloquent\Cosmos\ClassName\Factory\ClassNameFactoryInterface;
use Eloquent\Cosmos\ClassName\QualifiedClassNameInterface;
use Eloquent\Cosmos\Exception\UndefinedClassException;
use Eloquent\Cosmos\Resolution\Context\Factory\Exception\SourceCodeReadException;
use Eloquent\Cosmos\Resolution\Context\ResolutionContext;
use Eloquent\Cosmos\Resolution\Context\ResolutionContextInterface;
use Eloquent\Cosmos\Resolution\Parser\ResolutionContextParser;
use Eloquent\Cosmos\Resolution\Parser\ResolutionContextParserInterface;
use Eloquent\Cosmos\UseStatement\UseStatementInterface;
use Eloquent\Pathogen\FileSystem\FileSystemPath;
use Icecave\Isolator\Isolator;
use ReflectionClass;
use ReflectionException;

/**
 * Creates class name resolution contexts.
 */
class ResolutionContextFactory implements ResolutionContextFactoryInterface
{
    /**
     * Get a static instance of this factory.
     *
     * @return ResolutionContextFactoryInterface The static factory.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Construct a new class name resolution context factory.
     *
     * @param ClassNameFactoryInterface|null        $classNameFactory The class name factory to use.
     * @param ResolutionContextParserInterface|null $contextParser    The context parser to use.
     * @param Isolator|null                         $isolator         The isolator to use.
     */
    public function __construct(
        ClassNameFactoryInterface $classNameFactory = null,
        ResolutionContextParserInterface $contextParser = null,
        Isolator $isolator = null
    ) {
        if (null === $classNameFactory) {
            $classNameFactory = ClassNameFactory::instance();
        }
        $isolator = Isolator::get($isolator);
        if (null === $contextParser) {
            $contextParser = new ResolutionContextParser(
                $classNameFactory,
                null,
                null,
                null,
                $this,
                $isolator
            );
        }

        $this->classNameFactory = $classNameFactory;
        $this->contextParser = $contextParser;
        $this->isolator = $isolator;
    }

    /**
     * Get the class name factory.
     *
     * @return ClassNameFactoryInterface The class name factory.
     */
    public function classNameFactory()
    {
        return $this->classNameFactory;
    }

    /**
     * Get the resolution context parser.
     *
     * @return ResolutionContextParserInterface The resolution context parser.
     */
    public function contextParser()
    {
        return $this->contextParser;
    }

    /**
     * Construct a new class name resolution context.
     *
     * @param QualifiedClassNameInterface|null  $primaryNamespace The namespace.
     * @param array<UseStatementInterface>|null $useStatements    The use statements.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     */
    public function create(
        QualifiedClassNameInterface $primaryNamespace = null,
        array $useStatements = null
    ) {
        return new ResolutionContext(
            $primaryNamespace,
            $useStatements,
            $this->classNameFactory()
        );
    }

    /**
     * Construct a new class name resolution context by inspecting the source
     * code of the supplied class.
     *
     * @param QualifiedClassNameInterface $className The class.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws UndefinedClassException    If the class does not exist.
     * @throws SourceCodeReadException    If the source code cannot be read.
     */
    public function createFromClass(QualifiedClassNameInterface $className)
    {
        try {
            $reflector = new ReflectionClass($className->string());
        } catch (ReflectionException $e) {
            throw new UndefinedClassException($className, $e);
        }

        return $this->createFromReflector($reflector);
    }

    /**
     * Construct a new class name resolution context by inspecting the source
     * code of the supplied class reflector.
     *
     * @param ReflectionClass $reflector The reflector.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws SourceCodeReadException    If the source code cannot be read.
     */
    public function createFromReflector(ReflectionClass $reflector)
    {
        $className = '\\' . $reflector->getName();

        $source = @$this->isolator()
            ->file_get_contents($reflector->getFileName());
        if (false === $source) {
            throw new SourceCodeReadException(
                $this->classNameFactory()->create($className),
                FileSystemPath::fromString($reflector->getFileName())
            );
        }

        $parsedContexts = $this->contextParser()->parseSource($source);
        $context = null;
        foreach ($parsedContexts as $parsedContext) {
            foreach ($parsedContext->classNames() as $thisClassName) {
                if ($thisClassName->string() === $className) {
                    $context = $parsedContext->context();

                    break 2;
                }
            }
        }

        if (null === $context) {
            throw new SourceCodeReadException(
                $this->classNameFactory()->create($className),
                FileSystemPath::fromString($reflector->getFileName())
            );
        }

        return $context;
    }

    /**
     * Get the isolator.
     *
     * @return Isolator The isolator.
     */
    protected function isolator()
    {
        return $this->isolator;
    }

    private static $instance;
    private $classNameFactory;
    private $contextParser;
    private $isolator;
}
