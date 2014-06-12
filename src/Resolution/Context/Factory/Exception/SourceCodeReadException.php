<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Context\Factory\Exception;

use Eloquent\Cosmos\Symbol\SymbolInterface;
use Eloquent\Pathogen\FileSystem\FileSystemPathInterface;
use Exception;

/**
 * The source code could not be read from the file system.
 */
final class SourceCodeReadException extends Exception
{
    /**
     * Construct a new source code read exception.
     *
     * @param SymbolInterface         $symbol The symbol.
     * @param FileSystemPathInterface $path   The path.
     * @param Exception|null          $cause  The cause, if available.
     */
    public function __construct(
        SymbolInterface $symbol,
        FileSystemPathInterface $path,
        Exception $cause = null
    ) {
        $this->symbol = $symbol;
        $this->path = $path;

        parent::__construct(
            sprintf(
                'Unable to read the source code for symbol %s from %s.',
                var_export($symbol->string(), true),
                var_export($path->string(), true)
            ),
            0,
            $cause
        );
    }

    /**
     * Get the symbol.
     *
     * @return SymbolInterface The symbol.
     */
    public function symbol()
    {
        return $this->symbol;
    }

    /**
     * Get the path.
     *
     * @return FileSystemPathInterface The path.
     */
    public function path()
    {
        return $this->path;
    }

    private $symbol;
    private $path;
}
