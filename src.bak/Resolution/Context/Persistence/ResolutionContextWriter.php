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
use Eloquent\Cosmos\Resolution\Context\Renderer\ResolutionContextRenderer;
use Eloquent\Cosmos\Resolution\Context\Renderer\ResolutionContextRendererInterface;
use Eloquent\Cosmos\Resolution\Context\ResolutionContextInterface;
use Exception;

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
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Construct a new resolution context writer.
     *
     * @param ResolutionContextRendererInterface|null $contextRenderer The renderer to use.
     * @param StreamEditorInterface|null              $streamEditor    The stream editor to use.
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
    ) {
        $stream = $this->createStringStream($data, $path);
        $this->replaceContextInStream($stream, $parsedContext, $context, $path);
        $this->streamEditor()->seek($stream, 0, null, $path);

        return $this->streamEditor()->readAll($stream, $path);
    }

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
    ) {
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
        $this->streamEditor()->stripTrailingWhitespace($stream, $path);
    }

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
    ) {
        $stream = $this->streamEditor()->open($path, 'rb+');

        $error = null;
        try {
            $this->replaceContextInStream(
                $stream,
                $parsedContext,
                $context,
                $path
            );
        } catch (Exception $error) {
            // re-throw after cleanup
        }

        $this->streamEditor()->close($stream, $path);

        if ($error) {
            throw $error;
        }
    }

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
    ) {
        $stream = $this->createStringStream($data, $path);
        $this->replaceContextsInStream(
            $stream,
            $parsedContexts,
            $contexts,
            $path
        );
        $this->streamEditor()->seek($stream, 0, null, $path);

        return $this->streamEditor()->readAll($stream, $path);
    }

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
    ) {
        $replacements = array();
        foreach ($parsedContexts as $index => $parsedContext) {
            $replacements = array_merge(
                $replacements,
                $this->replacementsForContext(
                    $stream,
                    $path,
                    $parsedContext,
                    $contexts[$index]
                )
            );
        }

        $this->streamEditor()->replaceMultiple($stream, $replacements, $path);
        $this->streamEditor()->stripTrailingWhitespace($stream, $path);
    }

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
    ) {
        $stream = $this->streamEditor()->open($path, 'rb+');

        $error = null;
        try {
            $this->replaceContextsInStream(
                $stream,
                $parsedContexts,
                $contexts,
                $path
            );
        } catch (Exception $error) {
            // re-throw after cleanup
        }

        $this->streamEditor()->close($stream, $path);

        if ($error) {
            throw $error;
        }
    }

    /**
     * Here be dragons...
     */
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
            $parsedNsBodyOffset) = $this->inspectContext($stream, $parsedContext);

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
            $useStatementsPrefix = '';
            $useStatementsSuffix = '';

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

                if (!$parsedHasNsSymbol) {
                    $useStatementsSuffix = "\n\n";
                } else {
                    $useStatementsSuffix = '';
                }
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

                    if ($newHasUseStatements && !$parsedHasUseStatements) {
                        $nsSymbolSuffix = ';';
                    } else {
                        $nsSymbolSuffix = ";\n\n" . $useStatementsIndent;
                    }
                }
            }
        } else {
            if ($context->primaryNamespace()->isRoot()) {
                if ($isAlternate) {
                    $nsSymbolOffset = $parsedContext->offset() + 9;
                    $nsSymbolSize = $parsedNsSymbolEndOffset - $nsSymbolOffset +
                        1;
                    $nsSymbolPrefix = '';
                    $nsSymbolSuffix = '';
                } else {
                    $nsSymbolOffset = $parsedContext->offset();
                    $nsSymbolSize = $useStatementsOffset - $nsSymbolOffset;
                }
            } else {
                $nsSymbolOffset = $parsedNsSymbolOffset;
                $nsSymbolSize = $parsedNsSymbolEndOffset - $nsSymbolOffset + 1;
                $nsSymbolPrefix = '';
                $nsSymbolSuffix = '';
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
        $parsedNsBodyOffset = $parsedContext->offset();
        $isAlternate = false;
        $parsedNsSymbolSize = 0;
        $parsedNsSymbolOffset = null;
        $parsedNsSymbolEndOffset = null;

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

    private function createStringStream($data, $path)
    {
        $stream = $this->streamEditor()->open('php://temp', 'rb+');
        $this->streamEditor()->write($stream, $data, $path);

        return $stream;
    }

    private static $instance;
    private $contextRenderer;
    private $streamEditor;
}
