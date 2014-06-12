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

use Eloquent\Cosmos\Exception\UndefinedSymbolException;
use Eloquent\Cosmos\Resolution\Context\Factory\Exception\SourceCodeReadException;
use Eloquent\Cosmos\Resolution\Context\Parser\ResolutionContextParser;
use Eloquent\Cosmos\Resolution\Context\Parser\ResolutionContextParserInterface;
use Eloquent\Cosmos\Resolution\Context\ResolutionContext;
use Eloquent\Cosmos\Resolution\Context\ResolutionContextInterface;
use Eloquent\Cosmos\Symbol\Factory\SymbolFactory;
use Eloquent\Cosmos\Symbol\Factory\SymbolFactoryInterface;
use Eloquent\Cosmos\Symbol\QualifiedSymbolInterface;
use Eloquent\Cosmos\Symbol\SymbolInterface;
use Eloquent\Cosmos\UseStatement\UseStatementInterface;
use Eloquent\Pathogen\FileSystem\FileSystemPath;
use Icecave\Isolator\Isolator;
use ReflectionClass;
use ReflectionFunction;
use ReflectionObject;

/**
 * Creates symbol resolution contexts.
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
     * Construct a new symbol resolution context factory.
     *
     * @param SymbolFactoryInterface|null           $symbolFactory The symbol factory to use.
     * @param ResolutionContextParserInterface|null $contextParser The context parser to use.
     * @param Isolator|null                         $isolator      The isolator to use.
     */
    public function __construct(
        SymbolFactoryInterface $symbolFactory = null,
        ResolutionContextParserInterface $contextParser = null,
        Isolator $isolator = null
    ) {
        if (null === $symbolFactory) {
            $symbolFactory = SymbolFactory::instance();
        }
        $isolator = Isolator::get($isolator);
        if (null === $contextParser) {
            $contextParser = new ResolutionContextParser(
                $symbolFactory,
                null,
                null,
                null,
                $this,
                $isolator
            );
        }

        $this->symbolFactory = $symbolFactory;
        $this->contextParser = $contextParser;
        $this->isolator = $isolator;
    }

    /**
     * Get the symbol factory.
     *
     * @return SymbolFactoryInterface The symbol factory.
     */
    public function symbolFactory()
    {
        return $this->symbolFactory;
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
     * Construct a new symbol resolution context.
     *
     * @param QualifiedSymbolInterface|null     $primaryNamespace The namespace.
     * @param array<UseStatementInterface>|null $useStatements    The use statements.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     */
    public function create(
        QualifiedSymbolInterface $primaryNamespace = null,
        array $useStatements = null
    ) {
        return new ResolutionContext(
            $primaryNamespace,
            $useStatements,
            $this->symbolFactory()
        );
    }

    /**
     * Construct a new symbol resolution context by inspecting the source code
     * of the supplied object's class.
     *
     * @param object $object The object.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws SourceCodeReadException    If the source code cannot be read.
     */
    public function createFromObject($object)
    {
        return $this->createFromClass(new ReflectionObject($object));
    }

    /**
     * Construct a new symbol resolution context by inspecting the source code
     * of the supplied symbol.
     *
     * @param SymbolInterface|string $symbol The symbol.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws UndefinedSymbolException   If the symbol does not exist.
     * @throws SourceCodeReadException    If the source code cannot be read.
     */
    public function createFromSymbol($symbol)
    {
        if ($symbol instanceof SymbolInterface) {
            $symbol = $symbol->string();
        }

        if (class_exists($symbol)) {
            return $this->createFromClass(new ReflectionClass($symbol));
        }
        if (function_exists($symbol)) {
            return $this->createFromFunction(new ReflectionFunction($symbol));
        }

        throw new UndefinedSymbolException(
            $this->symbolFactory()->createRuntime($symbol)
        );
    }

    /**
     * Construct a new symbol resolution context by inspecting the source code
     * of the supplied class or object reflector.
     *
     * @param ReflectionClass $class The class or object reflector.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws SourceCodeReadException    If the source code cannot be read.
     */
    public function createFromClass(ReflectionClass $class)
    {
        $symbol = '\\' . $class->getName();

        $source = @$this->isolator()->file_get_contents($class->getFileName());
        if (false === $source) {
            throw new SourceCodeReadException(
                $this->symbolFactory()->create($symbol),
                FileSystemPath::fromString($class->getFileName())
            );
        }

        $parsedContexts = $this->contextParser()->parseSource($source);
        $context = null;
        foreach ($parsedContexts as $parsedContext) {
            foreach ($parsedContext->symbols() as $thisSymbol) {
                if ($thisSymbol->string() === $symbol) {
                    $context = $parsedContext->context();

                    break 2;
                }
            }
        }

        if (null === $context) {
            throw new SourceCodeReadException(
                $this->symbolFactory()->create($symbol),
                FileSystemPath::fromString($class->getFileName())
            );
        }

        return $context;
    }

    /**
     * Construct a new symbol resolution context by inspecting the source code
     * of the supplied function reflector.
     *
     * @param ReflectionFunction $function The function reflector.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws SourceCodeReadException    If the source code cannot be read.
     */
    public function createFromFunction(ReflectionFunction $function)
    {
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
    private $symbolFactory;
    private $contextParser;
    private $isolator;
}
