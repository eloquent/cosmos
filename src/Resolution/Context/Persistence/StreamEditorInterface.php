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
}
