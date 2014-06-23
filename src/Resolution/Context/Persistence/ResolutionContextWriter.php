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
use Eloquent\Cosmos\Exception\ReadException;
use Eloquent\Cosmos\Exception\WriteException;
use Eloquent\Cosmos\Resolution\Context\Parser\Element\ParsedResolutionContextInterface;
use Eloquent\Cosmos\Resolution\Context\Renderer\ResolutionContextRenderer;
use Eloquent\Cosmos\Resolution\Context\Renderer\ResolutionContextRendererInterface;
use Eloquent\Cosmos\Resolution\Context\ResolutionContextInterface;
use Eloquent\Pathogen\FileSystem\FileSystemPath;
use Eloquent\Pathogen\FileSystem\FileSystemPathInterface;
use ErrorException;
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
        // return $this->doReplaceMultiple(
        //     $stream,
        //     $size,
        //     $this->replacementsForContext($parsedContext, $context),
        //     $path
        // );
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

    // private function replacementsForContext(
    //     ParsedResolutionContextInterface $parsedContext,
    //     ResolutionContextInterface $context
    //) {
    //     if (null === $parsedContext->namespaceSymbolOffset()) {
    //         $renderedNamespaceSymbol = ' ' . $context->primaryNamespace()
    //             ->accept($this->contextRenderer());
    //         $namespaceSymbolOffset = $parsedContext->offset() + 9;
    //         $namespaceSymbolSize = 0;
    //     } else {
    //         $renderedNamespaceSymbol = $context->primaryNamespace()
    //             ->accept($this->contextRenderer());
    //         $namespaceSymbolOffset = $parsedContext->namespaceSymbolOffset();
    //         $namespaceSymbolSize = $parsedContext->namespaceSymbolSize();
    //     }

    //     $renderedUseStatements = $this->contextRenderer()
    //         ->renderUseStatements($context->useStatements());

    //     if (count($parsedContext->useStatements()) > 0) {
    //         list($useStatementsOffset, $useStatementsSize, $indent) = $this
    //             ->useStatementsStats($parsedContext->useStatements());

    //         $renderedUseStatements = $this
    //             ->doIndent($renderedUseStatements, str_repeat(' ', $indent));
    //     } else {
    //         $renderedUseStatements = $this
    //             ->doIndent("\n" . $renderedUseStatements, '    ') . "\n";
    //         $useStatementsOffset = $parsedContext->namespaceBodyOffset();
    //         $useStatementsSize = 0;
    //     }

    //     return array(
    //         array(
    //             $namespaceSymbolOffset,
    //             $namespaceSymbolSize,
    //             $renderedNamespaceSymbol,
    //         ),
    //         array(
    //             $useStatementsOffset,
    //             $useStatementsSize,
    //             $renderedUseStatements
    //         ),
    //     );
    // }

    // private function useStatementsStats(array $useStatements)
    // {
    //     $startOffset = $endOffset = $indent = null;
    //     foreach ($useStatements as $useStatement) {
    //         if (
    //             null === $startOffset ||
    //             $useStatement->offset() < $startOffset
    //         ) {
    //             $startOffset = $useStatement->offset();
    //             $indent = $useStatement->position()->column() - 1;
    //         }

    //         $statementEndOffset = $useStatement->offset() +
    //             $useStatement->size() - 1;
    //         if (null === $endOffset || $statementEndOffset > $endOffset) {
    //             $endOffset = $statementEndOffset;
    //         }
    //     }

    //     return array($startOffset, $endOffset - $startOffset + 1, $indent);
    // }

    // private function doIndent($source, $indent)
    // {
    //     $lines = preg_split('/(\r|\n|\r\n)/', rtrim($source, "\r\n"));

    //     return implode("\n" . $indent, $lines);
    // }

    // private function doReplaceMultiple(
    //     $stream,
    //     $streamSize,
    //     array $replacements,
    //     $path
    //) {
    //     $this->assertStreamIsSeekable($stream, $path);

    //     usort(
    //         $replacements,
    //         function ($left, $right) {
    //             return $right[0] - $left[0];
    //         }
    //     );

    //     foreach ($replacements as $replacement) {
    //         list($offset, $replaceSize, $data) = $replacement;
    //         $streamSize = $this->doReplace(
    //             $stream,
    //             $streamSize,
    //             $offset,
    //             $replaceSize,
    //             $data,
    //             $path
    //         );
    //     }
    // }

    // private function doReplace(
    //     $stream,
    //     $streamSize,
    //     $offset,
    //     $replaceSize,
    //     $replacement,
    //     $path
    //) {
    //     $bufferSize = $this->bufferSize();
    //     $replacementSize = strlen($replacement);
    //     $sizeDifference = $replacementSize - $replaceSize;

    //     if ($sizeDifference > 0) {
    //         $replaceEnd = $offset + $replaceSize - 1;
    //         $i = $streamSize - $bufferSize;
    //         while (true) {
    //             if ($i < $replaceEnd) {
    //                 $i = $replaceEnd;
    //             }

    //             $this->doSeekOrExpand($stream, $streamSize, $i, $path);
    //             $data = $this->doRead($stream, $bufferSize, $path);
    //             $this->doSeekOrExpand($stream, $streamSize, $i + $sizeDifference, $path);
    //             $this->doWrite($stream, $data, $path);

    //             if ($i === $replaceEnd) {
    //                 break;
    //             } else {
    //                 $i -= $bufferSize;
    //             }
    //         }

    //         $streamSize += $sizeDifference;
    //     }

    //     $this->doSeekOrExpand($stream, $streamSize, $offset - 1, $path);
    //     $result = $this->doWrite($stream, $replacement, $path);

    //     if ($sizeDifference < 0) {
    //         $i = $offset + $replaceSize - 1;
    //         while (true) {
    //             $this->doSeekOrExpand($stream, $streamSize, $i, $path);
    //             $data = $this->doRead($stream, $bufferSize, $path);
    //             $this->doSeekOrExpand($stream, $streamSize, $i + $sizeDifference, $path);
    //             $this->doWrite($stream, $data, $path);

    //             if (strlen($data) < $bufferSize) {
    //                 break;
    //             } else {
    //                 $i += $bufferSize;
    //             }
    //         }

    //         $streamSize += $sizeDifference;

    //         $this->doTruncate($stream, $streamSize, $path);
    //     }

    //     return $streamSize;
    // }

    private function assertStreamIsSeekable($stream, $path)
    {
        $metaData = @$this->isolator()->stream_get_meta_data($stream);

        if (false === $metaData) {
            if (is_string($path)) {
                $path = FileSystemPath::fromString($path);
            }

            throw new ReadException($path, $this->lastError());
        }

        if (!$metaData['seekable']) {
            if (is_string($path)) {
                $path = FileSystemPath::fromString($path);
            }

            throw new WriteException('Stream is not seekable.', $path);
        }
    }

    private function doSeekOrExpand($stream, $size, $offset, $path)
    {
        if ($offset < $size) {
            return $this->doSeek($stream, $offset, $path);
        }

        $result = $this->doSeek($stream, $size, $path);
        $this->doWrite($stream, str_repeat("\0", $offset - $size), $path);

        return $result;
    }

    private function doSeek($stream, $offset, $path)
    {
        // echo 'Seeking to ' . $offset . PHP_EOL;

        $result = @$this->isolator()->fseek($stream, $offset);

        if (-1 === $result || false === $result) {
            if (is_string($path)) {
                $path = FileSystemPath::fromString($path);
            }

            throw new ReadException($path, $this->lastError());
        }

        return $result;
    }

    private function doTruncate($stream, $size, $path)
    {
        // echo 'Truncating to ' . $size . PHP_EOL;

        $result = @$this->isolator()->ftruncate($stream, $size);

        if (false === $result) {
            if (is_string($path)) {
                $path = FileSystemPath::fromString($path);
            }

            throw new WriteException($path, $this->lastError());
        }

        return $result;
    }

    private function doRead($stream, $size, $path)
    {
        // echo 'Reading ' . $size . PHP_EOL;

        $result = @$this->isolator()->fread($stream, $size);

        if (false === $result) {
            if (is_string($path)) {
                $path = FileSystemPath::fromString($path);
            }

            throw new ReadException($path, $this->lastError());
        }

        return $result;
    }

    private function doWrite($stream, $data, $path)
    {
        // echo 'Writing ' . var_export($data, true) . PHP_EOL;

        $result = @$this->isolator()->fwrite($stream, $data);

        if (false === $result) {
            if (is_string($path)) {
                $path = FileSystemPath::fromString($path);
            }

            throw new WriteException($path, $this->lastError());
        }

        return $result;
    }

    private function lastError()
    {
        $lastError = $this->isolator()->error_get_last();

        if (null === $lastError) {
            return null;
        }

        return new ErrorException(
            $lastError['message'],
            0,
            $lastError['type'],
            $lastError['file'],
            $lastError['line']
        );
    }

    private static $instance;
    private $contextRenderer;
    private $bufferSize;
    private $isolator;
}
