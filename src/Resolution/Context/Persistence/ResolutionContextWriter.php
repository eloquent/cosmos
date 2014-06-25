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

use Eloquent\Cosmos\Exception\IoExceptionInterface;
use Eloquent\Cosmos\Resolution\Context\Parser\Element\ParsedResolutionContextInterface;
use Eloquent\Cosmos\Resolution\Context\Renderer\ResolutionContextRenderer;
use Eloquent\Cosmos\Resolution\Context\Renderer\ResolutionContextRendererInterface;
use Eloquent\Cosmos\Resolution\Context\ResolutionContextInterface;
use Eloquent\Pathogen\FileSystem\FileSystemPathInterface;
use Icecave\Isolator\Isolator;

/**
 * Writes symbol resolution contexts to files and streams.
 */
class ResolutionContextWriter implements ResolutionContextWriterInterface
{
    /**
     * Get a static instance of this writer.
     *
     * @return ResolutionContextWriterInterface The static writer.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Construct a new resolution context writer.
     *
     * @param ResolutionContextRendererInterface|null $contextRenderer The renderer to use.
     * @param integer|null                            $bufferSize      The buffer size to use.
     * @param Isolator|null                           $isolator        The isolator to use.
     */
    public function __construct(
        ResolutionContextRendererInterface $contextRenderer = null,
        $bufferSize = null,
        Isolator $isolator = null
    ) {
        if (null === $contextRenderer) {
            $contextRenderer = ResolutionContextRenderer::instance();
        }
        if (null === $bufferSize) {
            $bufferSize = 1024;
        }

        $this->contextRenderer = $contextRenderer;
        $this->bufferSize = $bufferSize;
        $this->isolator = Isolator::get($isolator);
    }

    /**
     * Get the resolution context renderer.
     *
     * @return ResolutionContextRendererInterface The resolution context renderer.
     */
    public function contextRenderer()
    {
        return $this->contextRenderer;
    }

    /**
     * Get the buffer size.
     *
     * @return integer The buffer size.
     */
    public function bufferSize()
    {
        return $this->bufferSize;
    }

    /**
     * Replace a symbol resolution context in a stream.
     *
     * @param stream                              $stream        The stream.
     * @param integer                             $size          The stream size.
     * @param ParsedResolutionContextInterface    $parsedContext The parsed resolution context.
     * @param ResolutionContextInterface          $context       The replacement resolution context.
     * @param FileSystemPathInterface|string|null $path          The path, if known.
     *
     * @throws IoExceptionInterface If a stream operation fails.
     */
    public function replaceContextInStream(
        $stream,
        $size,
        ParsedResolutionContextInterface $parsedContext,
        ResolutionContextInterface $context,
        $path = null
    ) {
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
    private $contextRenderer;
    private $bufferSize;
    private $isolator;
}
