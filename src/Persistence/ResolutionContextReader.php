<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Persistence;

use Eloquent\Cosmos\Exception\ReadException;
use Eloquent\Cosmos\Exception\UndefinedResolutionContextException;
use Eloquent\Cosmos\Exception\UndefinedSymbolException;
use Eloquent\Cosmos\Parser\ResolutionContextParser;
use Eloquent\Cosmos\Parser\TokenNormalizer;
use Eloquent\Cosmos\Resolution\Context\ResolutionContextFactory;
use Eloquent\Cosmos\Resolution\Context\ResolutionContextFactoryInterface;
use Eloquent\Cosmos\Resolution\Context\ResolutionContextInterface;
use Eloquent\Cosmos\Symbol\SymbolFactory;
use Eloquent\Cosmos\Symbol\SymbolFactoryInterface;
use Eloquent\Cosmos\Symbol\SymbolInterface;
use ErrorException;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionObject;

/**
 * Reads resolution contexts from various sources.
 *
 * @api
 */
class ResolutionContextReader implements ResolutionContextReaderInterface
{
    /**
     * Get a static instance of this reader.
     *
     * @api
     *
     * @return ResolutionContextReaderInterface The static reader.
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self(
                TokenNormalizer::instance(),
                ResolutionContextParser::instance(),
                ResolutionContextFactory::instance(),
                SymbolFactory::instance()
            );
        }

        return self::$instance;
    }

    /**
     * Construct a new resolution context reader.
     *
     * @api
     *
     * @param TokenNormalizer                   $tokenNormalizer The token normalizer.
     * @param ResolutionContextParser           $contextParser   The resolution context parser.
     * @param ResolutionContextFactoryInterface $contextFactory  The resolution context factory.
     * @param SymbolFactoryInterface            $symbolFactory   The symbol factory.
     */
    public function __construct(
        TokenNormalizer $tokenNormalizer,
        ResolutionContextParser $contextParser,
        ResolutionContextFactoryInterface $contextFactory,
        SymbolFactoryInterface $symbolFactory
    ) {
        $this->tokenNormalizer = $tokenNormalizer;
        $this->contextParser = $contextParser;
        $this->contextFactory = $contextFactory;
        $this->symbolFactory = $symbolFactory;
    }

    /**
     * Create a new symbol resolution context for the supplied object.
     *
     * @api
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
     * @api
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
            $symbol = \strval($symbol);
        }

        try {
            $class = new ReflectionClass($symbol);
        } catch (ReflectionException $e) {
            throw new UndefinedSymbolException(
                'class',
                $this->symbolFactory->createFromRuntimeString($symbol),
                $e
            );
        }

        return $this->readFromClass($class);
    }

    /**
     * Create a new symbol resolution context for the supplied function symbol.
     *
     * @api
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
            $symbol = \strval($symbol);
        }

        try {
            $function = new ReflectionFunction($symbol);
        } catch (ReflectionException $e) {
            throw new UndefinedSymbolException(
                'function',
                $this->symbolFactory->createFromRuntimeString($symbol),
                $e
            );
        }

        return $this->readFromFunction($function);
    }

    /**
     * Create a new symbol resolution context for the supplied class or object
     * reflector.
     *
     * @api
     *
     * @param ReflectionClass $class The class or object reflector.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws ReadException              If the source code cannot be read.
     * @throws UndefinedSymbolException   If the symbol cannot be found in the source code.
     */
    public function readFromClass(ReflectionClass $class)
    {
        $path = $class->getFileName();

        if (false === $path) {
            return $this->contextFactory->createContext();
        }

        $name = '\\' . $class->getName();
        $tokens = $this->readFile($path);

        foreach ($this->contextParser->parseContexts($tokens) as $context) {
            foreach ($context->symbols as $symbol) {
                switch ($symbol->type) {
                    case 'class':
                    case 'interface':
                    case 'trait':
                        if (\strval($symbol) === $name) {
                            return $context;
                        }
                }
            }
        }

        throw new UndefinedSymbolException(
            'class',
            $this->symbolFactory->createFromString($name)
        );
    }

    /**
     * Create a new symbol resolution context for the supplied function
     * reflector.
     *
     * @api
     *
     * @param ReflectionFunction $function The function reflector.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws ReadException              If the source code cannot be read.
     * @throws UndefinedSymbolException   If the symbol cannot be found in the source code.
     */
    public function readFromFunction(ReflectionFunction $function)
    {
        $path = $function->getFileName();

        if (false === $path) {
            return $this->contextFactory->createContext();
        }

        $name = '\\' . $function->getName();
        $tokens = $this->readFile($path);

        foreach ($this->contextParser->parseContexts($tokens) as $context) {
            foreach ($context->symbols as $symbol) {
                if (
                    'function' === $symbol->type &&
                    \strval($symbol) === $name
                ) {
                    return $context;
                }
            }
        }

        throw new UndefinedSymbolException(
            'function',
            $this->symbolFactory->createFromString($name)
        );
    }

    /**
     * Create the first context found in a file.
     *
     * @api
     *
     * @param string $path The path.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws ReadException              If the source code cannot be read.
     */
    public function readFromFile($path)
    {
        return $this->readFromFileByIndex($path, 0);
    }

    /**
     * Create the context found at the specified index in a file.
     *
     * @api
     *
     * @param string  $path  The path.
     * @param integer $index The index.
     *
     * @return ResolutionContextInterface          The newly created resolution context.
     * @throws ReadException                       If the source code cannot be read.
     * @throws UndefinedResolutionContextException If there is no resolution context at the specified index.
     */
    public function readFromFileByIndex($path, $index)
    {
        $tokens = $this->readFile($path);
        $contexts = $this->contextParser->parseContexts($tokens);

        if (isset($contexts[$index])) {
            return $contexts[$index];
        }

        throw new UndefinedResolutionContextException($index, $path);
    }

    /**
     * Create the context found at the specified position in a file.
     *
     * @api
     *
     * @param string  $path   The path.
     * @param integer $line   The line.
     * @param integer $column The column.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws ReadException              If the source code cannot be read.
     */
    public function readFromFileByPosition($path, $line, $column = 1)
    {
        $tokens = $this->readFile($path);
        $contexts = $this->contextParser->parseContexts($tokens);
        $seen = false;

        foreach ($contexts as $index => $context) {
            if ($context->line > $line) {
                $seen = true;

                break;
            }
            if ($context->line === $line && $context->column > $column) {
                $seen = true;

                break;
            }
        }

        if (!$seen) {
            return \array_pop($contexts);
        }

        if ($index < 1) {
            return $this->contextFactory->createContext();
        }

        return $contexts[$index - 1];
    }

    /**
     * Create the first context found in a stream.
     *
     * @api
     *
     * @param stream      $stream The stream.
     * @param string|null $path   The path, if known.
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
     * @api
     *
     * @param stream      $stream The stream.
     * @param integer     $index  The index.
     * @param string|null $path   The path, if known.
     *
     * @return ResolutionContextInterface          The newly created resolution context.
     * @throws ReadException                       If the source code cannot be read.
     * @throws UndefinedResolutionContextException If there is no resolution context at the specified index.
     */
    public function readFromStreamByIndex($stream, $index, $path = null)
    {
        $tokens = $this->readStream($stream, $path);
        $contexts = $this->contextParser->parseContexts($tokens);

        if (isset($contexts[$index])) {
            return $contexts[$index];
        }

        throw new UndefinedResolutionContextException($index, $path);
    }

    /**
     * Create the context found at the specified position in a stream.
     *
     * @api
     *
     * @param stream      $stream The stream.
     * @param integer     $line   The line.
     * @param integer     $column The column.
     * @param string|null $path   The path, if known.
     *
     * @return ResolutionContextInterface The newly created resolution context.
     * @throws ReadException              If the source code cannot be read.
     */
    public function readFromStreamByPosition(
        $stream,
        $line,
        $column = 1,
        $path = null
    ) {
        $tokens = $this->readStream($stream, $path);
        $contexts = $this->contextParser->parseContexts($tokens);
        $seen = false;

        foreach ($contexts as $index => $context) {
            if ($context->line > $line) {
                $seen = true;

                break;
            }
            if ($context->line === $line && $context->column > $column) {
                $seen = true;

                break;
            }
        }

        if (!$seen) {
            return \array_pop($contexts);
        }

        if ($index < 1) {
            return $this->contextFactory->createContext();
        }

        return $contexts[$index - 1];
    }

    private function readFile($path)
    {
        $source = @\file_get_contents($path);

        if (false !== $source) {
            return $this->tokenNormalizer
                ->normalizeTokens(\token_get_all($source));
        }

        throw new ReadException($path, $this->lastError());
    }

    private function readStream($stream, $path = null)
    {
        $source = @\stream_get_contents($stream);

        if (false !== $source) {
            return $this->tokenNormalizer
                ->normalizeTokens(\token_get_all($source));
        }

        throw new ReadException($path, $this->lastError());
    }

    private function lastError()
    {
        if ($lastError = \error_get_last()) {
            return new ErrorException(
                $lastError['message'],
                0,
                $lastError['type'],
                $lastError['file'],
                $lastError['line']
            );
        }

        return null; // @codeCoverageIgnore
    }

    private static $instance;
    private $tokenNormalizer;
    private $contextParser;
    private $contextFactory;
    private $symbolFactory;
    private $fileGetContents;
    private $streamGetContents;
    private $errorGetLast;
}
