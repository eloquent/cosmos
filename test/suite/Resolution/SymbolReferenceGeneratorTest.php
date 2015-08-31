<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution;

use Eloquent\Cosmos\Resolution\Context\ResolutionContext;
use Eloquent\Cosmos\Symbol\Symbol;
use Eloquent\Cosmos\Symbol\SymbolFactory;
use Eloquent\Cosmos\UseStatement\UseStatement;
use Eloquent\Liberator\Liberator;
use PHPUnit_Framework_TestCase;

/**
 * @covers \Eloquent\Cosmos\Resolution\SymbolReferenceGenerator
 */
class SymbolReferenceGeneratorTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->symbolFactory = new SymbolFactory();
        $this->subject = new SymbolReferenceGenerator($this->symbolFactory);

        $this->primaryNamespace = Symbol::fromString('\SymbolA\SymbolB');
        $this->useStatements = array(
            UseStatement::fromSymbol(Symbol::fromString('\SymbolC\SymbolD')),
            UseStatement::fromSymbol(Symbol::fromString('\SymbolE\SymbolF'), 'SymbolG'),
            UseStatement::fromSymbol(Symbol::fromString('\SymbolH\SymbolI')),
            UseStatement::fromSymbol(Symbol::fromString('\SymbolH\SymbolI\SymbolJ')),
            UseStatement::fromSymbol(Symbol::fromString('\SymbolA\SymbolB\SymbolAC')),

            UseStatement::fromSymbol(Symbol::fromString('\SymbolM\SymbolN'), null, 'function'),
            UseStatement::fromSymbol(Symbol::fromString('\SymbolU\SymbolV'), null, 'const'),
        );
        $this->context = new ResolutionContext($this->primaryNamespace, $this->useStatements);
    }

    public function referenceToData()
    {
        //                                                 symbol                                      type        expected
        return array(
            'Primary namespace'                   => array('\SymbolA\SymbolB',                         null,       '\SymbolA\SymbolB'),
            'Primary namespace +1'                => array('\SymbolA\SymbolB\SymbolC',                 null,       'SymbolC'),
            'Primary namespace +2'                => array('\SymbolA\SymbolB\SymbolC\SymbolD',         null,       'SymbolC\SymbolD'),
            'Primary namespace +3'                => array('\SymbolA\SymbolB\SymbolC\SymbolD\SymbolE', null,       'SymbolC\SymbolD\SymbolE'),
            'Use statement'                       => array('\SymbolC\SymbolD',                         null,       'SymbolD'),
            'Use statement +1'                    => array('\SymbolC\SymbolD\SymbolE',                 null,       'SymbolD\SymbolE'),
            'Use statement +2'                    => array('\SymbolC\SymbolD\SymbolE\SymbolF',         null,       'SymbolD\SymbolE\SymbolF'),
            'Alias'                               => array('\SymbolE\SymbolF',                         null,       'SymbolG'),
            'Alias +1'                            => array('\SymbolE\SymbolF\SymbolH',                 null,       'SymbolG\SymbolH'),
            'Alias +2'                            => array('\SymbolE\SymbolF\SymbolH\SymbolI',         null,       'SymbolG\SymbolH\SymbolI'),
            'Shortest use statement'              => array('\SymbolH\SymbolI\SymbolJ',                 null,       'SymbolJ'),
            'Use statement not too short'         => array('\SymbolH\SymbolI\SymbolG',                 null,       'SymbolI\SymbolG'),
            'No relevant statements'              => array('\Foo\Bar\Baz',                             null,       '\Foo\Bar\Baz'),
            'Use statement better than namespace' => array('\SymbolA\SymbolB\SymbolAC\SymbolAD',       null,       'SymbolAC\SymbolAD'),
            'Avoid use statement clash'           => array('\SymbolA\SymbolB\SymbolD',                 null,       'namespace\SymbolD'),
            'Avoid use statement clash + N'       => array('\SymbolA\SymbolB\SymbolD\SymbolE\SymbolF', null,       'namespace\SymbolD\SymbolE\SymbolF'),
            'Avoid use alias clash'               => array('\SymbolA\SymbolB\SymbolG',                 null,       'namespace\SymbolG'),
            'Avoid use alias clash + N'           => array('\SymbolA\SymbolB\SymbolG\SymbolE\SymbolF', null,       'namespace\SymbolG\SymbolE\SymbolF'),

            'Use statement (function)'            => array('\SymbolM\SymbolN',                         'function', 'SymbolN'),
            'Use statement (constant)'            => array('\SymbolU\SymbolV',                         'const',    'SymbolV'),
        );
    }

    /**
     * @dataProvider referenceToData
     */
    public function testReferenceTo($symbolString, $type, $expected)
    {
        $this->assertSame(
            $expected,
            strval($this->subject->referenceTo($this->context, Symbol::fromString($symbolString), $type))
        );
    }

    public function testInstance()
    {
        $class = get_class($this->subject);
        $liberatedClass = Liberator::liberateClass($class);
        $liberatedClass->instance = null;
        $actual = $class::instance();

        $this->assertInstanceOf($class, $actual);
        $this->assertSame($actual, $class::instance());
    }
}
