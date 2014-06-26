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

        $this->streamEditor()->replaceMultiple(
            $stream,
            $this->replacementsForContext(
                $stream,
                $path,
                $parsedContext,
                $context
            ),
            $path
        );
    }

    private function replacementsForContext(
        $stream,
        $path,
        $parsedContext,
        $context
    ) {
        $replacements = array();

        list(
            $isAlternate,
            $parsedNsSymbolOffset,
            $parsedNsSymbolEndOffset,
            $parsedNsBodyOffset,
        ) = $this->inspectContext($stream, $parsedContext);

        $contextIndent = $this->streamEditor()
            ->findIndentByOffset($stream, $parsedContext->offset(), $path);
        $parsedHasNsSymbol = !$parsedContext->primaryNamespace()->isRoot();
        $newHasNsSymbol = !$context->primaryNamespace()->isRoot();
        $parsedUseStatements = $parsedContext->useStatements();
        $parsedHasUseStatements = count($parsedUseStatements) > 0;
        $newUseStatements = $context->useStatements();
        $newHasUseStatements = count($newUseStatements) > 0;

        if ($parsedHasUseStatements) {
            $useStatementsIndent = $this->streamEditor()->findIndentByOffset(
                $stream,
                $parsedUseStatements[0]->offset(),
                $path
            );
            $useStatementsPrefix = $useStatementsSuffix = '';

            if ($newHasUseStatements) {
                $useStatementsOffset = $parsedUseStatements[0]->offset();
                $useStatementsSize = $parsedContext->offset() +
                    $parsedContext->size() - $useStatementsOffset;
            } else {
                $useStatementsOffset = $parsedNsBodyOffset + 1;
                $useStatementsSize = $parsedContext->offset() +
                    $parsedContext->size() - $parsedNsBodyOffset - 1;
            }
        } else {
            $useStatementsOffset = $parsedNsBodyOffset + 1;
            $useStatementsSize = 0;

            if ($isAlternate) {
                $useStatementsIndent = $contextIndent . '    ';
                $useStatementsPrefix = "\n" . $useStatementsIndent;
                $useStatementsSuffix = "\n";
            } else {
                $useStatementsIndent = $contextIndent;
                $useStatementsPrefix = "\n\n" . $useStatementsIndent;
                $useStatementsSuffix = '';
            }
        }

        if ($newHasUseStatements) {
            $useStatementsReplacement =
                $useStatementsPrefix .
                $this->transformLines(
                    $this->contextRenderer()
                        ->renderUseStatements($newUseStatements),
                    $useStatementsIndent
                ) .
                $useStatementsSuffix;
        } else {
            $useStatementsReplacement = '';
        }

        if ($parsedHasUseStatements || $newHasUseStatements) {
            $replacements[] = array(
                $useStatementsOffset,
                $useStatementsSize,
                $useStatementsReplacement,
            );
        }

        if ($parsedContext->primaryNamespace()->isRoot()) {
            if (!$context->primaryNamespace()->isRoot()) {
                if ($isAlternate) {
                    $nsSymbolOffset = $parsedContext->offset() + 9;
                    $nsSymbolSize = 0;
                    $nsSymbolPrefix = ' ';
                    $nsSymbolSuffix = '';
                } else {
                    $nsSymbolOffset = $useStatementsOffset;
                    $nsSymbolSize = 0;
                    $nsSymbolPrefix = 'namespace ';
                    $nsSymbolSuffix = ";\n\n" . $useStatementsIndent;
                }
            }
        } else {
            if ($context->primaryNamespace()->isRoot()) {
                if ($isAlternate) {
                    $nsSymbolOffset = $parsedContext->offset() + 9;
                    $nsSymbolSize = $parsedNsSymbolEndOffset - $nsSymbolOffset +
                        1;
                    $nsSymbolPrefix = $nsSymbolSuffix = '';
                } else {
                    $nsSymbolOffset = $parsedContext->offset();
                    $nsSymbolSize = $useStatementsOffset - $nsSymbolOffset;
                }
            } else {
                $nsSymbolOffset = $parsedNsSymbolOffset;
                $nsSymbolSize = $parsedNsSymbolEndOffset - $nsSymbolOffset + 1;
                $nsSymbolPrefix = $nsSymbolSuffix = '';
            }
        }

        if ($newHasNsSymbol) {
            $nsSymbolReplacement =
                $nsSymbolPrefix .
                $context->primaryNamespace()->accept($this->contextRenderer()) .
                $nsSymbolSuffix;
        } else {
            $nsSymbolReplacement = '';
        }

        if ($parsedHasNsSymbol || $newHasNsSymbol) {
            $replacements[] = array(
                $nsSymbolOffset,
                $nsSymbolSize,
                $nsSymbolReplacement,
            );
        }

        return $replacements;
    }

    private function transformLines($lines, $indent)
    {
        $lines = preg_split('/(\r|\n|\r\n)/', trim($lines));

        return implode("\n" . $indent, $lines);
    }

    private function inspectContext($stream, $parsedContext)
    {
        $state = static::STATE_START;
        $isAlternate = false;
        $parsedNsSymbolSize = $parsedNsBodyOffset = 0;
        $parsedNsSymbolOffset = $parsedNsSymbolEndOffset = null;

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
                            $parsedNsSymbolOffset = $token[4];
                            $parsedNsSymbolEndOffset = $token[5];

                            break;

                        case '{':
                            $isAlternate = true;
                            $parsedNsBodyOffset = $token[5];

                            break;
                    }

                    break;

                case static::STATE_NAMESPACE_NAME:
                    switch ($token[0]) {
                        case T_STRING:
                        case T_NS_SEPARATOR:
                            if ($token[5] > $parsedNsSymbolEndOffset) {
                                $parsedNsSymbolEndOffset = $token[5];
                            }

                            break;

                        case '{':
                            $isAlternate = true;

                        case ';':
                            $state = static::STATE_START;
                            $parsedNsBodyOffset = $token[5];

                            break;
                    }

                    break;
            }
        }

        return array(
            $isAlternate,
            $parsedNsSymbolOffset,
            $parsedNsSymbolEndOffset,
            $parsedNsBodyOffset,
        );
    }

    private static $instance;
    private $contextRenderer;
    private $streamEditor;
}
