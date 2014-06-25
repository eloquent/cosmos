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
use Eloquent\Pathogen\FileSystem\FileSystemPath;
use Eloquent\Pathogen\FileSystem\FileSystemPathInterface;

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
     */
    public function __construct(
        ResolutionContextRendererInterface $contextRenderer = null,
        StreamEditorInterface $streamEditor = null
    ) {
        if (null === $contextRenderer) {
            $contextRenderer = ResolutionContextRenderer::instance();
        }
        if (null === $streamEditor) {
            $streamEditor = StreamEditor::instance();
        }

        $this->contextRenderer = $contextRenderer;
        $this->streamEditor = $streamEditor;
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
     * Get the stream editor.
     *
     * @return StreamEditorInterface The stream editor.
     */
    public function streamEditor()
    {
        return $this->streamEditor;
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
        if (is_string($path)) {
            $path = FileSystemPath::fromString($path);
        }

        $replacements = array();

        $this->streamEditor()->replaceMultiple($stream, $replacements, $path);
    }

    private static $instance;
    private $contextRenderer;
    private $streamEditor;
}
