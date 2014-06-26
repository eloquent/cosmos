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
    const STATE_START = 0;
    const STATE_NAMESPACE = 1;
    const STATE_NAMESPACE_NAME = 2;

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
     * @param ParsedResolutionContextInterface    $parsedContext The parsed resolution context.
     * @param ResolutionContextInterface          $context       The replacement resolution context.
     * @param FileSystemPathInterface|string|null $path          The path, if known.
     *
     * @throws IoExceptionInterface If a stream operation fails.
     */
    public function replaceContextInStream(
        $stream,
        ParsedResolutionContextInterface $parsedContext,
        ResolutionContextInterface $context,
        $path = null
    ) {
        if (is_string($path)) {
            $path = FileSystemPath::fromString($path);
        }

        $replacements = array();

        list(
            $namespaceSymbolOffset,
            $namespaceSymbolSize,
        ) = $this->inspectContext($stream, $parsedContext);

        // if ($isAlternate) {
        //     $this->handleAlternate();
        // } else {
            $this->handleRegular(
                $stream,
                $path,
                $parsedContext,
                $context,
                $replacements,
                $namespaceSymbolOffset,
                $namespaceSymbolSize
            );
        // }

        $this->streamEditor()->replaceMultiple($stream, $replacements, $path);
    }

    private function handleRegular(
        $stream,
        $path,
        $parsedContext,
        $context,
        &$replacements,
        $namespaceSymbolOffset,
        $namespaceSymbolSize
    ) {
        if ($parsedContext->primaryNamespace()->isRoot()) {
            if (!$context->primaryNamespace()->isRoot()) {

            }
        } else {
            if ($context->primaryNamespace()->isRoot()) {

            } elseif (
                $parsedContext->primaryNamespace()->atoms() !==
                $context->primaryNamespace()->atoms()
            ) {
                $replacements[] = array(
                    $namespaceSymbolOffset,
                    $namespaceSymbolSize,
                    $context->primaryNamespace()
                        ->accept($this->contextRenderer()),
                );
            }
        }

        $parsedUseStatements = $parsedContext->useStatements();

        if (count($parsedUseStatements) > 0) {
            $useStatementsOffset = $parsedUseStatements[0]->offset();
            $useStatementsSize = $parsedContext->offset() +
                $parsedContext->size() - $useStatementsOffset;

            $useStatements = $this->transformLines(
                $this->contextRenderer()
                    ->renderUseStatements($context->useStatements()),
                $this->streamEditor()
                    ->findIndentByOffset($stream, $useStatementsOffset, $path)
            );

            $replacements[] = array(
                $useStatementsOffset,
                $useStatementsSize,
                $useStatements
            );
        } else {

        }
    }

    private function transformLines($lines, $indent)
    {
        $lines = preg_split('/(\r|\n|\r\n)/', trim($lines));

        return implode("\n" . $indent, $lines);
    }

    private function inspectContext($stream, $parsedContext)
    {
        $state = static::STATE_START;
        $namespaceSymbolSize = 0;
        $namespaceSymbolOffset = $namespaceSymbolEndOffset = null;

        foreach ($parsedContext->tokens() as $token) {
            switch ($state) {
                case static::STATE_START:
                    switch ($token[0]) {
                        case T_NAMESPACE:
                            $state = static::STATE_NAMESPACE;

                            break;
                    }

                    break;

                case static::STATE_NAMESPACE:
                    switch ($token[0]) {
                        case T_STRING:
                            $state = static::STATE_NAMESPACE_NAME;
                            $namespaceSymbolOffset = $token[4];
                            $namespaceSymbolEndOffset = $token[5];

                            break;
                    }

                    break;

                case static::STATE_NAMESPACE_NAME:
                    switch ($token[0]) {
                        case T_STRING:
                        case T_NS_SEPARATOR:
                        case T_WHITESPACE:
                            if ($token[5] > $namespaceSymbolEndOffset) {
                                $namespaceSymbolEndOffset = $token[5];
                            }

                            break;

                        case ';':
                            $state = static::STATE_START;

                            break;
                    }

                    break;
            }
        }

        if (null !== $namespaceSymbolOffset) {
            $namespaceSymbolSize = $namespaceSymbolEndOffset -
                $namespaceSymbolOffset + 1;
        }

        return array(
            $namespaceSymbolOffset,
            $namespaceSymbolSize,
        );
    }

    private static $instance;
    private $contextRenderer;
    private $streamEditor;
}
