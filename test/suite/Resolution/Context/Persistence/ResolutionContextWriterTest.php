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

use Eloquent\Cosmos\Resolution\Context\Parser\ResolutionContextParser;
use Eloquent\Cosmos\Resolution\Context\Renderer\ResolutionContextRenderer;
use Eloquent\Cosmos\Resolution\Context\ResolutionContext;
use Eloquent\Cosmos\Symbol\Symbol;
use Eloquent\Cosmos\UseStatement\UseStatement;
use Eloquent\Cosmos\UseStatement\UseStatementClause;
use Eloquent\Cosmos\UseStatement\UseStatementType;
use Eloquent\Liberator\Liberator;
use Icecave\Isolator\Isolator;
use PHPUnit_Framework_TestCase;
use Phake;

class ResolutionContextWriterTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->contextRenderer = new ResolutionContextRenderer;
        $this->isolator = Phake::partialMock(Isolator::className());
        $this->writer = new ResolutionContextWriter($this->contextRenderer, 10, $this->isolator);

        $this->source = <<<'EOD'
<?php

namespace NamespaceA \ NamespaceB \ NamespaceC
{
    use SymbolA \ SymbolB \ SymbolC as SymbolD ;

    use function SymbolE , SymbolF ;
}

namespace NamespaceD {}

namespace
{
    use SymbolG;
}

EOD;
        $this->stream = fopen('php://memory', 'wb+');
        $this->streamSize = strlen($this->source);
        fwrite($this->stream, $this->source);

        $this->contextParser = ResolutionContextParser::instance();
        $this->parsedContexts = $this->contextParser->parseSource($this->source);
        $this->context = new ResolutionContext(
            Symbol::fromString('\NamespaceX\NamespaceY'),
            array(
                UseStatement::create(
                    Symbol::fromString('\SymbolX\SymbolY'),
                    Symbol::fromString('SymbolZ'),
                    UseStatementType::CONSTANT()
                ),
                new UseStatement(
                    array(
                        new UseStatementClause(Symbol::fromString('\SymbolT\SymbolU')),
                        new UseStatementClause(Symbol::fromString('\SymbolV\SymbolW')),
                    )
                ),
            )
        );
    }

    protected function tearDown()
    {
        parent::tearDown();

        fclose($this->stream);
    }

    public function testConstructor()
    {
        $this->assertSame($this->contextRenderer, $this->writer->contextRenderer());
        $this->assertSame(10, $this->writer->bufferSize());
    }

    public function testConstructorDefaults()
    {
        $this->writer = new ResolutionContextWriter;

        $this->assertSame(ResolutionContextRenderer::instance(), $this->writer->contextRenderer());
        $this->assertSame(1024, $this->writer->bufferSize());
    }

    public function testReplaceContextInStreamFirstContext()
    {
        $this->writer->replaceContextInStream(
            $this->stream,
            $this->streamSize,
            $this->parsedContexts[0],
            $this->context
        );
        fseek($this->stream, 0);
        $actual = stream_get_contents($this->stream);
        $expected = <<<'EOD'
<?php

namespace NamespaceX\NamespaceY
{
    use const SymbolX\SymbolY as SymbolZ;
    use SymbolT\SymbolU, SymbolV\SymbolW;
}

namespace NamespaceD {}

namespace
{
    use SymbolG;
}

EOD;

        $this->assertSame($expected, $actual);
    }

//     public function testReplaceContextInStreamMiddleContext()
//     {
//         $this->writer->replaceContextInStream(
//             $this->stream,
//             $this->streamSize,
//             $this->parsedContexts[1],
//             $this->context
//         );
//         fseek($this->stream, 0);
//         $actual = stream_get_contents($this->stream);
//         var_dump($actual);
//         $expected = <<<'EOD'
// <?php

// namespace NamespaceA \ NamespaceB \ NamespaceC
// {
//     use SymbolA \ SymbolB \ SymbolC as SymbolD ;

//     use function SymbolE , SymbolF ;
// }

// namespace NamespaceX\NamespaceY {
//     use const SymbolX\SymbolY as SymbolZ;
//     use SymbolT\SymbolU, SymbolV\SymbolW;
// }

// namespace
// {
//     use SymbolG;
// }

// EOD;

//         $this->assertSame($expected, $actual);
//     }

    public function testInstance()
    {
        $class = get_class($this->writer);
        $liberatedClass = Liberator::liberateClass($class);
        $liberatedClass->instance = null;
        $actual = $class::instance();

        $this->assertInstanceOf($class, $actual);
        $this->assertSame($actual, $class::instance());
    }
}
