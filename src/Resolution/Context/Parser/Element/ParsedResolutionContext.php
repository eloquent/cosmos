<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Context\Parser\Element;

use Eloquent\Cosmos\Resolution\Context\Parser\ParserPositionInterface;
use Eloquent\Cosmos\Resolution\Context\ResolutionContext;
use Eloquent\Cosmos\Resolution\Context\ResolutionContextInterface;
use Eloquent\Cosmos\Resolution\Context\ResolutionContextVisitorInterface;
use Eloquent\Cosmos\Symbol\QualifiedSymbolInterface;
use Eloquent\Cosmos\Symbol\SymbolReferenceInterface;
use Eloquent\Cosmos\Symbol\SymbolType;
use Eloquent\Cosmos\UseStatement\UseStatementInterface;
use Eloquent\Cosmos\UseStatement\UseStatementType;

/**
 * Represents a parsed resolution context and its related symbols.
 */
class ParsedResolutionContext extends AbstractParsedElement implements
    ParsedResolutionContextInterface
{
    /**
     * Construct a new parsed resolution context.
     *
     * @param ResolutionContextInterface|null   $context               The resolution context.
     * @param array<ParsedSymbolInterface>|null $symbols               The symbols defined under the parsed resolution context.
     * @param ParserPositionInterface|null      $position              The position.
     * @param integer|null                      $startOffset           The offset.
     * @param integer|null                      $size                  The element size in bytes.
     * @param integer|null                      $namespaceSymbolOffset The offset of the namespace symbol.
     * @param integer|null                      $namespaceSymbolSize   The namespace symbol size in bytes.
     */
    public function __construct(
        ResolutionContextInterface $context = null,
        array $symbols = null,
        ParserPositionInterface $position = null,
        $startOffset = null,
        $size = null,
        $namespaceSymbolOffset = null,
        $namespaceSymbolSize = null
    ) {
        if (null === $context) {
            $context = new ResolutionContext;
        }
        if (null === $symbols) {
            $symbols = array();
        }

        parent::__construct($position, $startOffset, $size);

        $this->context = $context;
        $this->symbols = $symbols;
        $this->namespaceSymbolOffset = $namespaceSymbolOffset;
        $this->namespaceSymbolSize = $namespaceSymbolSize;
    }

    /**
     * Get the resolution context.
     *
     * @return ResolutionContextInterface The resolution context.
     */
    public function context()
    {
        return $this->context;
    }

    /**
     * Get the symbols defined under the parsed resolution context.
     *
     * @return array<ParsedSymbolInterface> The defined symbols.
     */
    public function symbols()
    {
        return $this->symbols;
    }

    /**
     * Get the character offset for the start of the namespace symbol.
     *
     * @return integer|null The offset, or null if there is no namespace symbol.
     */
    public function namespaceSymbolOffset()
    {
        return $this->namespaceSymbolOffset;
    }

    /**
     * Get the size of the parsed namespace symbol.
     *
     * @return integer|null The namespace symbol size in bytes, or null if there is no namespace symbol.
     */
    public function namespaceSymbolSize()
    {
        return $this->namespaceSymbolSize;
    }

    /**
     * Get the namespace.
     *
     * @return QualifiedSymbolInterface The namespace.
     */
    public function primaryNamespace()
    {
        return $this->context()->primaryNamespace();
    }

    /**
     * Get the use statements.
     *
     * @return array<UseStatementInterface> The use statements.
     */
    public function useStatements()
    {
        return $this->context()->useStatements();
    }

    /**
     * Get the use statements by type.
     *
     * @param UseStatementType $type The type.
     *
     * @return array<UseStatementInterface> The use statements.
     */
    public function useStatementsByType(UseStatementType $type)
    {
        return $this->context()->useStatementsByType($type);
    }

    /**
     * Get the symbol associated with the supplied symbol reference's first
     * atom.
     *
     * @param SymbolReferenceInterface $symbol The symbol reference.
     * @param SymbolType|null          $type   The symbol type.
     *
     * @return QualifiedSymbolInterface|null The symbol, or null if no associated symbol exists.
     */
    public function symbolByFirstAtom(
        SymbolReferenceInterface $symbol,
        SymbolType $type = null
    ) {
        return $this->context()->symbolByFirstAtom($symbol, $type);
    }

    /**
     * Accept a visitor.
     *
     * @param ResolutionContextVisitorInterface $visitor The visitor to accept.
     *
     * @return mixed The result of visitation.
     */
    public function accept(ResolutionContextVisitorInterface $visitor)
    {
        return $visitor->visitResolutionContext($this);
    }

    private $context;
    private $symbols;
    private $namespaceSymbolOffset;
    private $namespaceSymbolSize;
}
