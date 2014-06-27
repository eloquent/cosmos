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
use Eloquent\Cosmos\Resolution\Context\Persistence\Exception\StreamOffsetOutOfBoundsException;
use ErrorException;
use Icecave\Isolator\Isolator;

/**
 * Performs modifications on streams.
 *
 * @internal
 */
class StreamEditor implements StreamEditorInterface
{
    /**
     * Get a static instance of this editor.
     *
     * @return StreamEditorInterface The static editor.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Construct a new stream editor.
     *
     * @param Isolator|null $isolator The isolator to use.
     */
    public function __construct($bufferSize = null, Isolator $isolator = null)
    {
        if (null === $bufferSize) {
            $bufferSize = 8192;
        }

        $this->bufferSize = $bufferSize;
        $this->isolator = Isolator::get($isolator);
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
     * Open a stream handle.
     *
     * @param string $path The path.
     * @param string $mode The stream mode.
     *
     * @return stream        The newly opened stream.
     * @throws ReadException If the stream cannot be opened.
     */
    public function open($path, $mode)
    {
        $stream = @$this->isolator->fopen($path, $mode);

        if (false === $stream) {
            throw new ReadException($path, $this->lastError());
        }

        return $stream;
    }

    /**
     * Close a stream handle.
     *
     * @param stream      $stream The stream to close.
     * @param string|null $path   The path, if known.
     *
     * @throws ReadException If the stream cannot be closed.
     */
    public function close($stream, $path = null)
    {
        $result = @$this->isolator->fclose($stream);

        if (false === $result) {
            throw new ReadException($path, $this->lastError());
        }
    }

    /**
     * Assert that the supplied stream is seekable.
     *
     * @param stream      $stream The stream to inspect.
     * @param string|null $path   The path, if known.
     *
     * @throws ReadException If the stream is not seekable.
     */
    public function assertStreamIsSeekable($stream, $path = null)
    {
        $metaData = $this->isolator->stream_get_meta_data($stream);

        if (false === $metaData) {
            throw new ReadException($path, $this->lastError());
        }

        if (!$metaData['seekable']) {
            throw new ReadException($path);
        }
    }

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
    public function seek($stream, $offset, $whence = null, $path = null)
    {
        if (null === $whence) {
            $whence = SEEK_SET;
        }

        $result = @$this->isolator->fseek($stream, $offset, $whence);

        if (-1 === $result || false === $result) {
            throw new ReadException($path, $this->lastError());
        }
    }

    /**
     * Read the current offset of a stream.
     *
     * @param stream      $stream The stream to read.
     * @param string|null $path   The path, if known.
     *
     * @return integer       The current offset.
     * @throws ReadException If the operation fails.
     */
    public function tell($stream, $path = null)
    {
        $result = @$this->isolator->ftell($stream);

        if (false === $result) {
            throw new ReadException($path, $this->lastError());
        }

        return $result;
    }

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
    public function read($stream, $size, $path = null)
    {
        $result = @$this->isolator->fread($stream, $size);

        if (false === $result) {
            throw new ReadException($path, $this->lastError());
        }

        return $result;
    }

    /**
     * Read all data from a stream.
     *
     * @param stream      $stream The stream to read.
     * @param string|null $path   The path, if known.
     *
     * @return string        The read data.
     * @throws ReadException If the operation fails.
     */
    public function readAll($stream, $path = null)
    {
        $result = @$this->isolator->stream_get_contents($stream);

        if (false === $result) {
            throw new ReadException($path, $this->lastError());
        }

        return $result;
    }

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
    public function write($stream, $data, $path = null)
    {
        $result = @$this->isolator->fwrite($stream, $data);

        if (false === $result) {
            throw new WriteException($path, $this->lastError());
        }

        return $result;
    }

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
    ) {
        $this->assertStreamIsSeekable($stream, $path);

        return $this->doReplace($stream, $offset, $size, $data, $path);
    }

    /**
     * Replace multiple sections of a stream.
     *
     * Each tuple entry is equivalent to the $offset, $size, and $replacement
     * parameters of a call to replace().
     *
     * @param stream                                         $stream       The stream to replace within.
     * @param array<tuple<integer,integer|null,string|null>> $replacements The replacements to perform.
     * @param string|null                                    $path         The path, if known.
     *
     * @return integer              The size difference in bytes.
     * @throws IoExceptionInterface If a stream operation cannot be performed.
     */
    public function replaceMultiple($stream, array $replacements, $path = null)
    {
        $this->assertStreamIsSeekable($stream, $path);

        $offsets = array();
        $indices = array();
        foreach ($replacements as $index => $replacement) {
            $offsets[$index] = $replacement[0];
            $indices[$index] = $index;
        }
        array_multisort(
            $offsets,
            SORT_NUMERIC,
            SORT_DESC,
            $indices,
            SORT_NUMERIC,
            SORT_ASC,
            $replacements
        );

        $sizeDifference = 0;
        foreach ($replacements as $replacement) {
            list($offset, $size, $data) = $replacement;

            $sizeDifference +=
                $this->doReplace($stream, $offset, $size, $data, $path);
        }

        return $sizeDifference;
    }

    /**
     * Strip trailing whitespace from all lines in a stream.
     *
     * @param stream      $stream The stream.
     * @param string|null $path   The path, if known.
     *
     * @throws IoExceptionInterface If a stream operation cannot be performed.
     */
    public function stripTrailingWhitespace($stream, $path = null)
    {
        $this->seek($stream, 0, null, $path);

        $replacements = array();
        $offset = $startOfWhitespace = $whitespaceSize = 0;
        while (true) {
            $data = $this->read($stream, $this->bufferSize, $path);

            foreach (str_split($data) as $character) {
                switch ($character) {
                    case ' ':
                    case "\t":
                        if (0 === $whitespaceSize) {
                            $startOfWhitespace = $offset;
                        }

                        $whitespaceSize++;

                        break;

                    case "\n":
                    case "\r":
                        if ($whitespaceSize > 0) {
                            $replacements[] = array(
                                $startOfWhitespace,
                                $whitespaceSize,
                                '',
                            );
                        }

                    default:
                        $whitespaceSize = 0;
                }

                $offset++;
            }

            if (strlen($data) < $this->bufferSize) {
                break;
            }
        }

        $this->replaceMultiple($stream, $replacements, $path);
    }

    /**
     * Find the start of the line by offset into a stream.
     *
     * @param stream      $stream The stream to inspect.
     * @param integer     $offset The offset to begin searching at.
     * @param string|null $path   The path, if known.
     *
     * @return integer       The offset of the start of the line.
     * @throws ReadException If the stream cannot be read.
     */
    public function findStartOfLineByOffset($stream, $offset, $path = null)
    {
        $this->seek($stream, $offset, null, $path);
        $nextCharacter = $this->read($stream, 1, $path);

        while ($offset > 0) {
            $offset--;
            $this->seek($stream, $offset, null, $path);
            $character = $this->read($stream, 1, $path);

            if (
                "\n" === $character ||
                ("\r" === $character && "\n" !== $nextCharacter)
            ) {
                $offset++;

                break;
            }

            $nextCharacter = $character;
        }

        return $offset;
    }

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
    public function findIndentByOffset($stream, $offset, $path = null)
    {
        $offset = $this->findStartOfLineByOffset($stream, $offset, $path);
        $this->seek($stream, $offset, null, $path);

        $character = '';
        $indent = '';
        do {
            $indent .= $character;
            $character = $this->read($stream, 1, $path);
        } while (' ' === $character || "\t" === $character);

        return $indent;
    }

    private function doReplace($stream, $offset, $size, $data, $path)
    {
        try {
            $this->seek($stream, $offset, null, $path);
        } catch (ReadException $e) {
            throw new StreamOffsetOutOfBoundsException($offset, $path, $e);
        }

        if (null === $data) {
            $data = '';
            $dataSize = 0;
        } else {
            $dataSize = strlen($data);
        }
        if (null === $size) {
            $size = $this->doSize($stream, $path) - $offset;
        }
        $delta = $dataSize - $size;

        if ($delta > 0) {
            $this->doExpand($stream, $delta, $offset + $size, $path);
        } elseif ($delta < 0) {
            $this->doContract($stream, $delta, $offset + $size, $path);
        }

        $this->seek($stream, $offset, null, $path);
        $this->write($stream, $data, $path);

        return $delta;
    }

    private function doExpand($stream, $delta, $offset, $path)
    {
        $size = $this->doSize($stream, $path);

        $i = $size - $this->bufferSize;
        do {
            if ($i < $offset) {
                $i = $offset;
            }

            $this->seek($stream, $i, null, $path);
            $data = $this->read($stream, $this->bufferSize, $path);
            $this->doSeekOrExpand($stream, $i + $delta, $size, $path);
            $this->write($stream, $data, $path);

            if ($i === $offset) {
                break;
            }
        } while ($i -= $this->bufferSize);
    }

    private function doContract($stream, $delta, $offset, $path)
    {
        $size = $this->doSize($stream, $path);

        $i = $offset;
        do {
            $this->seek($stream, $i, null, $path);
            $data = $this->read($stream, $this->bufferSize, $path);
            $this->seek($stream, $i + $delta, null, $path);
            $this->write($stream, $data, $path);

            if (strlen($data) < $this->bufferSize) {
                break;
            }
        } while ($i += $this->bufferSize);

        $this->doTruncate($stream, $size + $delta, $path);
    }

    private function doSeekOrExpand($stream, $offset, $size, $path)
    {
        if ($offset < $size) {
            return $this->seek($stream, $offset, null, $path);
        }

        $result = $this->seek($stream, $size, null, $path);

        $target = $offset - $size;
        $filled = 0;
        do {
            $fillSize = $this->bufferSize;
            if ($filled + $fillSize > $target) {
                $fillSize = $target - $filled;
            }

            $this->write($stream, str_repeat("\0", $fillSize), $path);
            $filled += $fillSize;
        } while ($filled < $target);

        return $result;
    }

    private function doSize($stream, $path)
    {
        $this->seek($stream, 0, SEEK_END, $path);

        return $this->tell($stream, $path);
    }

    private function doTruncate($stream, $size, $path)
    {
        $result = @$this->isolator->ftruncate($stream, $size);

        if (false === $result) {
            throw new WriteException($path, $this->lastError());
        }

        return $result;
    }

    private function lastError()
    {
        $lastError = $this->isolator->error_get_last();

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
    private $bufferSize;
    private $isolator;
}
