<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Context\Persistence;

use Eloquent\Cosmos\Exception\ReadException;
use Eloquent\Cosmos\Exception\UndefinedSymbolException;
use Eloquent\Cosmos\Resolution\Context\Factory\ResolutionContextFactory;
use Eloquent\Cosmos\Resolution\Context\Factory\ResolutionContextFactoryInterface;
use Eloquent\Cosmos\Resolution\Context\Parser\ParserPositionInterface;
use Eloquent\Cosmos\Resolution\Context\Parser\ResolutionContextParser;
use Eloquent\Cosmos\Resolution\Context\Parser\ResolutionContextParserInterface;
use Eloquent\Cosmos\Resolution\Context\Persistence\Exception\UndefinedResolutionContextException;
use Eloquent\Cosmos\Resolution\Context\ResolutionContextInterface;
use Eloquent\Cosmos\Symbol\Factory\SymbolFactory;
use Eloquent\Cosmos\Symbol\Factory\SymbolFactoryInterface;
use Eloquent\Cosmos\Symbol\SymbolInterface;
use Eloquent\Cosmos\Symbol\SymbolType;
use Eloquent\Pathogen\FileSystem\FileSystemPath;
use Eloquent\Pathogen\FileSystem\FileSystemPathInterface;
use Icecave\Isolator\Isolator;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionObject;

/**
 * Reads symbol resolution contexts from files and streams.
 */
class ResolutionContextReader implements ResolutionContextReaderInterface
{
    /**
     * Get a static instance of this reader.
     *
     * @return ResolutionContextReaderInterface The static reader.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Construct a new symbol resolution context reader.
     *
     * @param ResolutionContextParserInterface|null  $contextParser  The context parser to use.
     * @param ResolutionContextFactoryInterface|null $contextFactory The context factory to use.
     * @param SymbolFactoryInterface|null            $symbolFactory  The symbol factory to use.
     * @param Isolator|null                          $isolator       The isolator to use.
     */
    public function __construct(
        ResolutionContextParserInterface $contextParser = null,
        ResolutionContextFactoryInterface $contextFactory = null,
        SymbolFactoryInterface $symbolFactory = null,
        Isolator $isolator = null
    ) {
        if (null === $contextParser) {
            $contextParser = ResolutionContextParser::instance();
        }
        if (null === $contextFactory) {
            $contextFactory = ResolutionContextFactory::instance();
        }
        if (null === $symbolFactory) {
            $symbolFactory = SymbolFactory::instance();
        }

        $this->contextParser = $contextParser;
        $this->contextFactory = $contextFactory;
        $this->symbolFactory = $symbolFactory;
        $this->isolator = Isolator::get($isolator);
        $this->fileCache = $this->sourceCache = $this->classCache =
            $this->functionCache = array();
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
     * Get the resolution context factory.
     *
     * @return ResolutionContextFactoryInterface The resolution context factory.
     */
    public function contextFactory()
    {
        return $this->contextFactory;
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
     * Create a new symbol resolution context for the supplied object.
     *
     * @param object $object The object.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws ReadException              If the source code cannot be read.
     */
    public function readFromObject($object)
    {
        return $this->readFromClass(new ReflectionObject($object));
    }

    /**
     * Create a new symbol resolution context for the supplied class, interface,
     * or trait symbol.
     *
     * @param SymbolInterface|string $symbol The symbol.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws ReadException              If the source code cannot be read.
     * @throws UndefinedSymbolException   If the symbol does not exist, or cannot be found in the source code.
     */
    public function readFromSymbol($symbol)
    {
        if ($symbol instanceof SymbolInterface) {
            $symbol = $symbol->string();
        }

        try {
            $class = new ReflectionClass($symbol);
        } catch (ReflectionException $e) {
            throw new UndefinedSymbolException(
                $this->symbolFactory()->createRuntime($symbol),
                SymbolType::CLA55(),
                $e
            );
        }

        return $this->readFromClass($class);
    }

    /**
     * Create a new symbol resolution context for the supplied function symbol.
     *
     * @param SymbolInterface|string $symbol The symbol.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws ReadException              If the source code cannot be read.
     * @throws UndefinedSymbolException   If the symbol does not exist, or cannot be found in the source code.
     */
    public function readFromFunctionSymbol($symbol)
    {
        if ($symbol instanceof SymbolInterface) {
            $symbol = $symbol->string();
        }

        try {
            $function = new ReflectionFunction($symbol);
        } catch (ReflectionException $e) {
            throw new UndefinedSymbolException(
                $this->symbolFactory()->createRuntime($symbol),
                SymbolType::FUNCT1ON(),
                $e
            );
        }

        return $this->readFromFunction($function);
    }

    /**
     * Create a new symbol resolution context for the supplied class or object
     * reflector.
     *
     * @param ReflectionClass $class The class or object reflector.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws ReadException              If the source code cannot be read.
     * @throws UndefinedSymbolException   If the symbol cannot be found in the source code.
     */
    public function readFromClass(ReflectionClass $class)
    {
        $symbol = '\\' . $class->getName();

        if (!array_key_exists($symbol, $this->classCache)) {
            if (false === $class->getFileName()) {
                $this->classCache[$symbol] = $this->contextFactory()
                    ->createEmpty();
            } else {
                $context = $this->findBySymbolPredicate(
                    $this->parseFile($class->getFileName()),
                    function ($parsedSymbol) use ($symbol) {
                        return $parsedSymbol->symbol()->string() === $symbol &&
                            $parsedSymbol->type()->isType();
                    }
                );

                if (null === $context) {
                    throw new UndefinedSymbolException(
                        $this->symbolFactory()->create($symbol),
                        SymbolType::CLA55()
                    );
                }

                $this->classCache[$symbol] = $context;
            }
        }

        return $this->classCache[$symbol];
    }

    /**
     * Create a new symbol resolution context for the supplied function
     * reflector.
     *
     * @param ReflectionFunction $function The function reflector.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws ReadException              If the source code cannot be read.
     * @throws UndefinedSymbolException   If the symbol cannot be found in the source code.
     */
    public function readFromFunction(ReflectionFunction $function)
    {
        $symbol = '\\' . $function->getName();

        if (!array_key_exists($symbol, $this->functionCache)) {
            if (false === $function->getFileName()) {
                $this->functionCache[$symbol] = $this->contextFactory()
                    ->createEmpty();
            } else {
                $context = $this->findBySymbolPredicate(
                    $this->parseFile($function->getFileName()),
                    function ($parsedSymbol) use ($symbol) {
                        return $parsedSymbol->symbol()->string() === $symbol &&
                            SymbolType::FUNCT1ON() === $parsedSymbol->type();
                    }
                );

                if (null === $context) {
                    throw new UndefinedSymbolException(
                        $this->symbolFactory()->create($symbol),
                        SymbolType::FUNCT1ON()
                    );
                }

                $this->functionCache[$symbol] = $context;
            }
        }

        return $this->functionCache[$symbol];
    }

    /**
     * Create the first context found in a file.
     *
     * @param FileSystemPathInterface|string $path The path.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws ReadException              If the source code cannot be read.
     */
    public function readFromFile($path)
    {
        return $this->readFromFileByIndex($path, 0, $path);
    }

    /**
     * Create the context found at the specified index in a file.
     *
     * @param FileSystemPathInterface|string $path  The path.
     * @param integer                        $index The index.
     *
     * @return ResolutionContextInterface          The newly created resolution context.
     * @throws ReadException                       If the source code cannot be read.
     * @throws UndefinedResolutionContextException If there is no resolution context at the specified index.
     */
    public function readFromFileByIndex($path, $index)
    {
        return $this->findByIndex($this->parseFile($path), $index, $path);
    }

    /**
     * Create the context found at the specified position in a file.
     *
     * @param FileSystemPathInterface|string $path     The path.
     * @param ParserPositionInterface        $position The position.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws ReadException              If the source code cannot be read.
     */
    public function readFromFileByPosition(
        $path,
        ParserPositionInterface $position
    ) {
        return $this->findByPosition($this->parseFile($path), $position);
    }

    /**
     * Create the first context found in a stream.
     *
     * @param stream                              $stream The stream.
     * @param FileSystemPathInterface|string|null $path   The path, if known.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws ReadException              If the source code cannot be read.
     */
    public function readFromStream($stream, $path = null)
    {
        return $this->readFromStreamByIndex($stream, 0, $path);
    }

    /**
     * Create the context found at the specified index in a stream.
     *
     * @param stream                              $stream The stream.
     * @param integer                             $index  The index.
     * @param FileSystemPathInterface|string|null $path   The path, if known.
     *
     * @return ResolutionContextInterface          The newly created resolution context.
     * @throws ReadException                       If the source code cannot be read.
     * @throws UndefinedResolutionContextException If there is no resolution context at the specified index.
     */
    public function readFromStreamByIndex($stream, $index, $path = null)
    {
        return $this
            ->findByIndex($this->parseStream($stream, $path), $index, $path);
    }

    /**
     * Create the context found at the specified position in a stream.
     *
     * @param stream                              $stream   The stream.
     * @param ParserPositionInterface             $position The position.
     * @param FileSystemPathInterface|string|null $path     The path, if known.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws ReadException              If the source code cannot be read.
     */
    public function readFromStreamByPosition(
        $stream,
        ParserPositionInterface $position,
        $path = null
    ) {
        return $this
            ->findByPosition($this->parseStream($stream, $path), $position);
    }

    private function findBySymbolPredicate(array $contexts, $predicate)
    {
        $context = null;
        foreach ($contexts as $parsedContext) {
            foreach ($parsedContext->symbols() as $parsedSymbol) {
                if ($predicate($parsedSymbol)) {
                    $context = $parsedContext;

                    break 2;
                }
            }
        }

        return $context;
    }

    private function findByIndex(array $contexts, $index, $path = null)
    {
        if (!array_key_exists($index, $contexts)) {
            if (is_string($path)) {
                $path = FileSystemPath::fromString($path);
            }

            throw new UndefinedResolutionContextException($index, $path);
        }

        return $contexts[$index];
    }

    private function findByPosition(
        array $contexts,
        ParserPositionInterface $position
    ) {
        $context = null;
        foreach ($contexts as $parsedContext) {
            if ($this->positionIsAfter($parsedContext->position(), $position)) {
                break;
            }

            $context = $parsedContext;
        }

        if (null === $context) {
            $context = $this->contextFactory()->createEmpty();
        }

        return $context;
    }

    private function parseFile($path)
    {
        if ($path instanceof FileSystemPathInterface) {
            $path = $path->string();
        }

        if (!array_key_exists($path, $this->fileCache)) {
            $this->fileCache[$path] = $this->contextParser()
                ->parseSource($this->readFile($path));
        }

        return $this->fileCache[$path];
    }

    private function parseStream($stream, $path = null)
    {
        return $this->parseSource($this->readStream($stream, $path));
    }

    private function parseSource($source)
    {
        $hash = md5($source);
        if (!array_key_exists($hash, $this->sourceCache)) {
            $this->sourceCache[$hash] = $this->contextParser()
                ->parseSource($source);
        }

        return $this->sourceCache[$hash];
    }

    private function readFile($path)
    {
        $stream = @$this->isolator()->fopen($path, 'rb');
        if (false === $stream) {
            $lastError = $this->isolator()->error_get_last();

            throw new ReadException(
                $lastError['message'],
                FileSystemPath::fromString($path)
            );
        }

        $error = null;
        try {
            $source = $this->readStream($stream, $path);
        } catch (ReadException $error) {
            // re-throw after cleanup
        }

        @$this->isolator()->fclose($stream);

        if ($error) {
            throw $error;
        }

        return $source;
    }

    private function readStream($stream, $path = null)
    {
        $source = @$this->isolator()->stream_get_contents($stream);
        if (false === $source) {
            $lastError = $this->isolator()->error_get_last();
            if (is_string($path)) {
                $path = FileSystemPath::fromString($path);
            }

            throw new ReadException($lastError['message'], $path);
        }

        return $source;
    }

    private function positionIsAfter(
        ParserPositionInterface $left,
        ParserPositionInterface $right
    ) {
        $lineCompare = $left->line() - $right->line();

        if ($lineCompare > 0) {
            return true;
        }
        if ($lineCompare < 0) {
            return false;
        }

        return $left->column() > $right->column();
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
    private $contextParser;
    private $contextFactory;
    private $symbolFactory;
    private $isolator;
    private $fileCache;
    private $sourceCache;
    private $classCache;
    private $functionCache;
}
