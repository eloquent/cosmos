<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Context\Persistence;

use Eloquent\Cosmos\Exception\IoExceptionInterface;
use Eloquent\Cosmos\Resolution\Context\Parser\Element\ParsedResolutionContextInterface;
use Eloquent\Cosmos\Resolution\Context\ResolutionContextInterface;

/**
 * The interface implemented by symbol resolution context writers.
 */
interface ResolutionContextWriterInterface
{
    /**
     * Replace a symbol resolution context in a string.
     *
     * @param string                           $data          The string.
     * @param ParsedResolutionContextInterface $parsedContext The parsed resolution context.
     * @param ResolutionContextInterface       $context       The replacement resolution context.
     * @param string|null                      $path          The path, if known.
     *
     * @return string               The modified string.
     * @throws IoExceptionInterface If a stream operation fails.
     */
    public function replaceContextInString(
        $data,
        ParsedResolutionContextInterface $parsedContext,
        ResolutionContextInterface $context,
        $path = null
    );

    /**
     * Replace a symbol resolution context in a stream.
     *
     * @param stream                           $stream        The stream.
     * @param ParsedResolutionContextInterface $parsedContext The parsed resolution context.
     * @param ResolutionContextInterface       $context       The replacement resolution context.
     * @param string|null                      $path          The path, if known.
     *
     * @throws IoExceptionInterface If a stream operation fails.
     */
    public function replaceContextInStream(
        $stream,
        ParsedResolutionContextInterface $parsedContext,
        ResolutionContextInterface $context,
        $path = null
    );

    /**
     * Replace a symbol resolution context in a file.
     *
     * @param string                           $path          The path.
     * @param ParsedResolutionContextInterface $parsedContext The parsed resolution context.
     * @param ResolutionContextInterface       $context       The replacement resolution context.
     *
     * @throws IoExceptionInterface If a stream operation fails.
     */
    public function replaceContextInFile(
        $path,
        ParsedResolutionContextInterface $parsedContext,
        ResolutionContextInterface $context
    );

    /**
     * Replace a symbol resolution context in a string.
     *
     * The array keys of the replacement contexts must match those of the parsed
     * contexts.
     *
     * @param string                                  $data           The string.
     * @param array<ParsedResolutionContextInterface> $parsedContexts The parsed resolution contexts.
     * @param array<ResolutionContextInterface>       $contexts       The replacement resolution contexts.
     * @param string|null                             $path           The path, if known.
     *
     * @return string               The modified string.
     * @throws IoExceptionInterface If a stream operation fails.
     */
    public function replaceContextsInString(
        $data,
        array $parsedContexts,
        array $contexts,
        $path = null
    );

    /**
     * Replace a symbol resolution context in a stream.
     *
     * The array keys of the replacement contexts must match those of the parsed
     * contexts.
     *
     * @param stream                                  $stream         The stream.
     * @param array<ParsedResolutionContextInterface> $parsedContexts The parsed resolution contexts.
     * @param array<ResolutionContextInterface>       $contexts       The replacement resolution contexts.
     * @param string|null                             $path           The path, if known.
     *
     * @throws IoExceptionInterface If a stream operation fails.
     */
    public function replaceContextsInStream(
        $stream,
        array $parsedContexts,
        array $contexts,
        $path = null
    );

    /**
     * Replace a symbol resolution context in a file.
     *
     * The array keys of the replacement contexts must match those of the parsed
     * contexts.
     *
     * @param string                                  $path           The path.
     * @param array<ParsedResolutionContextInterface> $parsedContexts The parsed resolution contexts.
     * @param array<ResolutionContextInterface>       $contexts       The replacement resolution contexts.
     *
     * @throws IoExceptionInterface If a stream operation fails.
     */
    public function replaceContextsInFile(
        $path,
        array $parsedContexts,
        array $contexts
    );
}
