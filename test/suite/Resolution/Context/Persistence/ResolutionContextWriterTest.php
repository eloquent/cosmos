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
use PHPUnit_Framework_TestCase;

class ResolutionContextWriterTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->contextRenderer = new ResolutionContextRenderer;
        $this->streamEditor = new StreamEditor;
        $this->writer = new ResolutionContextWriter($this->contextRenderer, $this->streamEditor);

        $this->contextParser = ResolutionContextParser::instance();
        $this->useStatements = array(
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
        );
        $this->context = new ResolutionContext(Symbol::fromString('\NamespaceX\NamespaceY'), $this->useStatements);
        $this->contextGlobal = new ResolutionContext(null, $this->useStatements);
        $this->contextNoUse = new ResolutionContext(Symbol::fromString('\NamespaceX\NamespaceY'));
        $this->stream = fopen('php://memory', 'rb+');
    }

    protected function tearDown()
    {
        parent::tearDown();

        fclose($this->stream);
    }

    protected function streamFixture($data)
    {
        fwrite($this->stream, $data);
        $this->parsedContexts = $this->contextParser->parseSource($data);
    }

    public function testConstructor()
    {
        $this->assertSame($this->contextRenderer, $this->writer->contextRenderer());
        $this->assertSame($this->streamEditor, $this->writer->streamEditor());
    }

    public function testConstructorDefaults()
    {
        $this->writer = new ResolutionContextWriter;

        $this->assertSame(ResolutionContextRenderer::instance(), $this->writer->contextRenderer());
        $this->assertSame(StreamEditor::instance(), $this->writer->streamEditor());
    }

    public function testReplaceContextRegularShorter()
    {
        $this->streamFixture(
<<<'EOD'
<?php

    namespace NamespaceA \ NamespaceB \ NamespaceC ;

    use SymbolA \ SymbolB \ SymbolC as SymbolD ;

    use function SymbolE , SymbolF ;

EOD
        );
        $this->writer->replaceContextInStream($this->stream, $this->parsedContexts[0], $this->context);
        fseek($this->stream, 0);
        $actual = stream_get_contents($this->stream);
        $expected = <<<'EOD'
<?php

    namespace NamespaceX\NamespaceY;

    use const SymbolX\SymbolY as SymbolZ;
    use SymbolT\SymbolU, SymbolV\SymbolW;

EOD;

        $this->assertSame($expected, $actual);
    }

    public function testReplaceContextAlternateShorter()
    {
        $this->markTestIncomplete();

        $this->streamFixture(
<<<'EOD'
<?php

    namespace NamespaceA \ NamespaceB \ NamespaceC
    {
        use SymbolA \ SymbolB \ SymbolC as SymbolD ;

        use function SymbolE , SymbolF ;
    }

EOD
        );
        $this->writer->replaceContextInStream($this->stream, $this->parsedContexts[0], $this->context);
        fseek($this->stream, 0);
        $actual = stream_get_contents($this->stream);
        $expected = <<<'EOD'
<?php

    namespace NamespaceX\NamespaceY
    {
        use const SymbolX\SymbolY as SymbolZ;
        use SymbolT\SymbolU, SymbolV\SymbolW;
    }

EOD;

        $this->assertSame($expected, $actual);
    }

    public function testReplaceContextRegularLonger()
    {
        $this->markTestIncomplete();

        $this->streamFixture(
<<<'EOD'
<?php

    namespace NamespaceA ;

    use SymbolA ;

EOD
        );
        $this->writer->replaceContextInStream($this->stream, $this->parsedContexts[0], $this->context);
        fseek($this->stream, 0);
        $actual = stream_get_contents($this->stream);
        $expected = <<<'EOD'
<?php

    namespace NamespaceX\NamespaceY;

    use const SymbolX\SymbolY as SymbolZ;
    use SymbolT\SymbolU, SymbolV\SymbolW;

EOD;

        $this->assertSame($expected, $actual);
    }

    public function testReplaceContextAlternateLonger()
    {
        $this->markTestIncomplete();

        $this->streamFixture(
<<<'EOD'
<?php

    namespace NamespaceA
    {
        use SymbolA ;
    }

EOD
        );
        $this->writer->replaceContextInStream($this->stream, $this->parsedContexts[0], $this->context);
        fseek($this->stream, 0);
        $actual = stream_get_contents($this->stream);
        $expected = <<<'EOD'
<?php

    namespace NamespaceX\NamespaceY
    {
        use const SymbolX\SymbolY as SymbolZ;
        use SymbolT\SymbolU, SymbolV\SymbolW;
    }

EOD;

        $this->assertSame($expected, $actual);
    }

    public function testReplaceContextRegularFromGlobal()
    {
        $this->markTestIncomplete();

        $this->streamFixture(
<<<'EOD'
<?php

    use SymbolA \ SymbolB \ SymbolC as SymbolD ;

    use function SymbolE , SymbolF ;

EOD
        );
        $this->writer->replaceContextInStream($this->stream, $this->parsedContexts[0], $this->context);
        fseek($this->stream, 0);
        $actual = stream_get_contents($this->stream);
        $expected = <<<'EOD'
<?php

    namespace NamespaceX\NamespaceY;

    use const SymbolX\SymbolY as SymbolZ;
    use SymbolT\SymbolU, SymbolV\SymbolW;

EOD;

        $this->assertSame($expected, $actual);
    }

    public function testReplaceContextAlternateFromGlobal()
    {
        $this->markTestIncomplete();

        $this->streamFixture(
<<<'EOD'
<?php

    namespace
    {
        use SymbolA \ SymbolB \ SymbolC as SymbolD ;

        use function SymbolE , SymbolF ;
    }

EOD
        );
        $this->writer->replaceContextInStream($this->stream, $this->parsedContexts[0], $this->context);
        fseek($this->stream, 0);
        $actual = stream_get_contents($this->stream);
        $expected = <<<'EOD'
<?php

    namespace NamespaceX\NamespaceY
    {
        use const SymbolX\SymbolY as SymbolZ;
        use SymbolT\SymbolU, SymbolV\SymbolW;
    }

EOD;

        $this->assertSame($expected, $actual);
    }

    public function testReplaceContextRegularToGlobal()
    {
        $this->markTestIncomplete();

        $this->streamFixture(
<<<'EOD'
<?php

    namespace NamespaceA \ NamespaceB;

    use SymbolA \ SymbolB \ SymbolC as SymbolD ;

    use function SymbolE , SymbolF ;

EOD
        );
        $this->writer->replaceContextInStream($this->stream, $this->parsedContexts[0], $this->contextGlobal);
        fseek($this->stream, 0);
        $actual = stream_get_contents($this->stream);
        $expected = <<<'EOD'
<?php

    use const SymbolX\SymbolY as SymbolZ;
    use SymbolT\SymbolU, SymbolV\SymbolW;

EOD;

        $this->assertSame($expected, $actual);
    }

    public function testReplaceContextAlternateToGlobal()
    {
        $this->markTestIncomplete();

        $this->streamFixture(
<<<'EOD'
<?php

    namespace NamespaceA \ NamespaceB
    {
        use SymbolA \ SymbolB \ SymbolC as SymbolD ;

        use function SymbolE , SymbolF ;
    }

EOD
        );
        $this->writer->replaceContextInStream($this->stream, $this->parsedContexts[0], $this->contextGlobal);
        fseek($this->stream, 0);
        $actual = stream_get_contents($this->stream);
        $expected = <<<'EOD'
<?php

    namespace
    {
        use const SymbolX\SymbolY as SymbolZ;
        use SymbolT\SymbolU, SymbolV\SymbolW;
    }

EOD;

        $this->assertSame($expected, $actual);
    }

    public function testReplaceContextRegularFromNoUseStatements()
    {
        $this->markTestIncomplete();

        $this->streamFixture(
<<<'EOD'
<?php

    namespace NamespaceA;

EOD
        );
        $this->writer->replaceContextInStream($this->stream, $this->parsedContexts[0], $this->context);
        fseek($this->stream, 0);
        $actual = stream_get_contents($this->stream);
        $expected = <<<'EOD'
<?php

    namespace NamespaceX\NamespaceY;

    use const SymbolX\SymbolY as SymbolZ;
    use SymbolT\SymbolU, SymbolV\SymbolW;

EOD;

        $this->assertSame($expected, $actual);
    }

    public function testReplaceContextAlternateFromNoUseStatements()
    {
        $this->markTestIncomplete();

        $this->streamFixture(
<<<'EOD'
<?php

    namespace NamespaceA {}

EOD
        );
        $this->writer->replaceContextInStream($this->stream, $this->parsedContexts[0], $this->context);
        fseek($this->stream, 0);
        $actual = stream_get_contents($this->stream);
        $expected = <<<'EOD'
<?php

    namespace NamespaceX\NamespaceY {
        use const SymbolX\SymbolY as SymbolZ;
        use SymbolT\SymbolU, SymbolV\SymbolW;
    }

EOD;

        $this->assertSame($expected, $actual);
    }

    public function testReplaceContextRegularToNoUseStatements()
    {
        $this->markTestIncomplete();

        $this->streamFixture(
<<<'EOD'
<?php

    namespace NamespaceA;

    use SymbolA \ SymbolB \ SymbolC as SymbolD ;

    use function SymbolE , SymbolF ;

EOD
        );
        $this->writer->replaceContextInStream($this->stream, $this->parsedContexts[0], $this->contextNoUse);
        fseek($this->stream, 0);
        $actual = stream_get_contents($this->stream);
        $expected = <<<'EOD'
<?php

    namespace NamespaceX\NamespaceY;

EOD;

        $this->assertSame($expected, $actual);
    }

    public function testReplaceContextAlternateToNoUseStatements()
    {
        $this->markTestIncomplete();

        $this->streamFixture(
<<<'EOD'
<?php

    namespace NamespaceA
    {
        use SymbolA \ SymbolB \ SymbolC as SymbolD ;

        use function SymbolE , SymbolF ;
    }

EOD
        );
        $this->writer->replaceContextInStream($this->stream, $this->parsedContexts[0], $this->contextNoUse);
        fseek($this->stream, 0);
        $actual = stream_get_contents($this->stream);
        $expected = <<<'EOD'
<?php

    namespace NamespaceX\NamespaceY
    {
    }

EOD;

        $this->assertSame($expected, $actual);
    }

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
