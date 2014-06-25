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
use Eloquent\Pathogen\FileSystem\FileSystemPathInterface;

/**
 * The interface implemented by stream editors.
 *
 * @internal
 */
interface StreamEditorInterface
{
    /**
     * Assert that the supplied stream is seekable.
     *
     * @param stream                       $stream The stream to inspect.
     * @param FileSystemPathInterface|null $path   The path, if known.
     *
     * @throws ReadException If the stream is not seekable.
     */
    public function assertStreamIsSeekable(
        $stream,
        FileSystemPathInterface $path = null
    );

    /**
     * Seek to an offset on a stream.
     *
     * @param stream                       $stream The stream to seek on.
     * @param integer                      $offset The offset to seek to.
     * @param integer|null                 $whence The type of seek operation.
     * @param FileSystemPathInterface|null $path   The path, if known.
     *
     * @throws ReadException If the operation fails.
     */
    public function seek(
        $stream,
        $offset,
        $whence = null,
        FileSystemPathInterface $path = null
    );

    /**
     * Read the current offset of a stream.
     *
     * @param stream                       $stream The stream to read.
     * @param FileSystemPathInterface|null $path   The path, if known.
     *
     * @return integer       The current offset.
     * @throws ReadException If the operation fails.
     */
    public function tell($stream, FileSystemPathInterface $path = null);

    /**
     * Read from a stream.
     *
     * @param stream                       $stream The stream to read.
     * @param integer                      $size   The maximum amount of data to read.
     * @param FileSystemPathInterface|null $path   The path, if known.
     *
     * @return string        The read data.
     * @throws ReadException If the operation fails.
     */
    public function read($stream, $size, FileSystemPathInterface $path = null);

    /**
     * Replace a section of a stream.
     *
     * @param stream                       $stream The stream to replace within.
     * @param integer                      $offset The start byte offset for replacement.
     * @param integer|null                 $size   The amount of data to replace in bytes, or null to replace all subsequent data.
     * @param string|null                  $data   The data to replace into the stream, or null to simply remove data.
     * @param FileSystemPathInterface|null $path   The path, if known.
     *
     * @return integer              The size difference in bytes.
     * @throws IoExceptionInterface If a stream operation cannot be performed.
     */
    public function replace(
        $stream,
        $offset,
        $size = null,
        $data = null,
        FileSystemPathInterface $path = null
    );

    /**
     * Replace multiple sections of a stream.
     *
     * Each tuple entry is equivalent to the $offset, $size, and $data
     * parameters of a call to replace().
     *
     * @param stream                                         $stream       The stream to replace within.
     * @param array<tuple<integer,integer|null,string|null>> $replacements The replacements to perform.
     * @param FileSystemPathInterface|null                   $path         The path, if known.
     *
     * @return integer              The size difference in bytes.
     * @throws IoExceptionInterface If a stream operation cannot be performed.
     */
    public function replaceMultiple(
        $stream,
        array $replacements,
        FileSystemPathInterface $path = null
    );

    /**
     * Find the line indent by offset into a stream.
     *
     * @param stream                       $stream The stream to inspect.
     * @param integer                      $offset The offset to begin searching at.
     * @param FileSystemPathInterface|null $path   The path, if known.
     *
     * @return string        The indent.
     * @throws ReadException If the stream cannot be read.
     */
    public function findIndentByOffset(
        $stream,
        $offset,
        FileSystemPathInterface $path = null
    );
}
