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

/**
 * The interface implemented by stream editors.
 *
 * @internal
 */
interface StreamEditorInterface
{
    /**
     * Open a stream handle.
     *
     * @param string $path The path.
     * @param string $mode The stream mode.
     *
     * @return stream        The newly opened stream.
     * @throws ReadException If the stream cannot be opened.
     */
    public function open($path, $mode);

    /**
     * Close a stream handle.
     *
     * @param stream      $stream The stream to close.
     * @param string|null $path   The path, if known.
     *
     * @throws ReadException If the stream cannot be closed.
     */
    public function close($stream, $path = null);

    /**
     * Assert that the supplied stream is seekable.
     *
     * @param stream      $stream The stream to inspect.
     * @param string|null $path   The path, if known.
     *
     * @throws ReadException If the stream is not seekable.
     */
    public function assertStreamIsSeekable($stream, $path = null);

    /**
     * Seek to an offset on a stream.
     *
     * @param stream       $stream The stream to seek on.
     * @param integer      $offset The offset to seek to.
     * @param integer|null $whence The type of seek operation.
     * @param string|null  $path   The path, if known.
     *
     * @throws ReadException If the operation fails.
     */
    public function seek($stream, $offset, $whence = null, $path = null);

    /**
     * Read the current offset of a stream.
     *
     * @param stream      $stream The stream to read.
     * @param string|null $path   The path, if known.
     *
     * @return integer       The current offset.
     * @throws ReadException If the operation fails.
     */
    public function tell($stream, $path = null);

    /**
     * Read from a stream.
     *
     * @param stream      $stream The stream to read.
     * @param integer     $size   The maximum amount of data to read.
     * @param string|null $path   The path, if known.
     *
     * @return string        The read data.
     * @throws ReadException If the operation fails.
     */
    public function read($stream, $size, $path = null);

    /**
     * Read all data from a stream.
     *
     * @param stream      $stream The stream to read.
     * @param string|null $path   The path, if known.
     *
     * @return string        The read data.
     * @throws ReadException If the operation fails.
     */
    public function readAll($stream, $path = null);

    /**
     * Write to a stream.
     *
     * @param stream      $stream The stream to write to.
     * @param string      $data   The data to write.
     * @param string|null $path   The path, if known.
     *
     * @return integer        The number of bytes written.
     * @throws WriteException If the operation fails.
     */
    public function write($stream, $data, $path = null);

    /**
     * Replace a section of a stream.
     *
     * @param stream       $stream The stream to replace within.
     * @param integer      $offset The start byte offset for replacement.
     * @param integer|null $size   The amount of data to replace in bytes, or null to replace all subsequent data.
     * @param string|null  $data   The data to replace into the stream, or null to simply remove data.
     * @param string|null  $path   The path, if known.
     *
     * @return integer              The size difference in bytes.
     * @throws IoExceptionInterface If a stream operation cannot be performed.
     */
    public function replace(
        $stream,
        $offset,
        $size = null,
        $data = null,
        $path = null
    );

    /**
     * Replace multiple sections of a stream.
     *
     * Each tuple entry is equivalent to the $offset, $size, and $data
     * parameters of a call to replace().
     *
     * @param stream                                         $stream       The stream to replace within.
     * @param array<tuple<integer,integer|null,string|null>> $replacements The replacements to perform.
     * @param string|null                                    $path         The path, if known.
     *
     * @return integer              The size difference in bytes.
     * @throws IoExceptionInterface If a stream operation cannot be performed.
     */
    public function replaceMultiple($stream, array $replacements, $path = null);

    /**
     * Find the line indent by offset into a stream.
     *
     * @param stream      $stream The stream to inspect.
     * @param integer     $offset The offset to begin searching at.
     * @param string|null $path   The path, if known.
     *
     * @return string        The indent.
     * @throws ReadException If the stream cannot be read.
     */
    public function findIndentByOffset($stream, $offset, $path = null);
}
