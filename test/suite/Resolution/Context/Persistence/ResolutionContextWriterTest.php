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
        $this->path = '/path/to/file';
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

    public function replaceContextInData()
    {
        return array(
            'Shorter' => array( //--------------------------------------------------------------------------------------
                <<<'EOD'
<?php

    namespace NamespaceA \ NamespaceB \ NamespaceC ;

    use SymbolA \ SymbolB \ SymbolC as SymbolD ;

    use function SymbolE , SymbolF ;

    // some other code

    namespace NamespaceD;

EOD
                ,
                0,
                'context',
                <<<'EOD'
<?php

    namespace NamespaceX\NamespaceY ;

    use const SymbolX\SymbolY as SymbolZ;
    use SymbolT\SymbolU, SymbolV\SymbolW;

    // some other code

    namespace NamespaceD;

EOD
                ,
            ),

            'Shorter (alternate)' => array( //--------------------------------------------------------------------------
                <<<'EOD'
<?php

    namespace NamespaceA \ NamespaceB \ NamespaceC
    {
        use SymbolA \ SymbolB \ SymbolC as SymbolD ;

        use function SymbolE , SymbolF ;
    }

    // some other code

    namespace NamespaceD {}

EOD
                ,
                0,
                'context',
                <<<'EOD'
<?php

    namespace NamespaceX\NamespaceY
    {
        use const SymbolX\SymbolY as SymbolZ;
        use SymbolT\SymbolU, SymbolV\SymbolW;
    }

    // some other code

    namespace NamespaceD {}

EOD
                ,
            ),

            'Longer' => array( //---------------------------------------------------------------------------------------
                <<<'EOD'
<?php

    namespace NamespaceA ;

    use SymbolA ;

    // some other code

    namespace NamespaceD;

EOD
                ,
                0,
                'context',
                <<<'EOD'
<?php

    namespace NamespaceX\NamespaceY ;

    use const SymbolX\SymbolY as SymbolZ;
    use SymbolT\SymbolU, SymbolV\SymbolW;

    // some other code

    namespace NamespaceD;

EOD
                ,
            ),

            'Longer (alternate)' => array( //---------------------------------------------------------------------------
                <<<'EOD'
<?php

    namespace NamespaceA
    {
        use SymbolA ;
    }

    // some other code

    namespace NamespaceD {}

EOD
                ,
                0,
                'context',
                <<<'EOD'
<?php

    namespace NamespaceX\NamespaceY
    {
        use const SymbolX\SymbolY as SymbolZ;
        use SymbolT\SymbolU, SymbolV\SymbolW;
    }

    // some other code

    namespace NamespaceD {}

EOD
                ,
            ),

            'From global' => array( //----------------------------------------------------------------------------------
                <<<'EOD'
<?php

    use SymbolA \ SymbolB \ SymbolC as SymbolD ;

    use function SymbolE , SymbolF ;

    // some other code

    namespace NamespaceD;

EOD
                ,
                0,
                'context',
                <<<'EOD'
<?php

    namespace NamespaceX\NamespaceY;

    use const SymbolX\SymbolY as SymbolZ;
    use SymbolT\SymbolU, SymbolV\SymbolW;

    // some other code

    namespace NamespaceD;

EOD
                ,
            ),

            'From global (alternate)' => array( //----------------------------------------------------------------------
                <<<'EOD'
<?php

    namespace
    {
        use SymbolA \ SymbolB \ SymbolC as SymbolD ;

        use function SymbolE , SymbolF ;
    }

    // some other code

    namespace NamespaceD {}

EOD
                ,
                0,
                'context',
                <<<'EOD'
<?php

    namespace NamespaceX\NamespaceY
    {
        use const SymbolX\SymbolY as SymbolZ;
        use SymbolT\SymbolU, SymbolV\SymbolW;
    }

    // some other code

    namespace NamespaceD {}

EOD
                ,
            ),

            'To global' => array( //------------------------------------------------------------------------------------
                <<<'EOD'
<?php

    namespace NamespaceA \ NamespaceB;

    use SymbolA \ SymbolB \ SymbolC as SymbolD ;

    use function SymbolE , SymbolF ;

    // some other code

    namespace NamespaceD;

EOD
                ,
                0,
                'contextGlobal',
                <<<'EOD'
<?php

    use const SymbolX\SymbolY as SymbolZ;
    use SymbolT\SymbolU, SymbolV\SymbolW;

    // some other code

    namespace NamespaceD;

EOD
                ,
            ),

            'To global (alternate)' => array( //------------------------------------------------------------------------
                <<<'EOD'
<?php

    namespace NamespaceA \ NamespaceB
    {
        use SymbolA \ SymbolB \ SymbolC as SymbolD ;

        use function SymbolE , SymbolF ;
    }

    // some other code

    namespace NamespaceD {}

EOD
                ,
                0,
                'contextGlobal',
                <<<'EOD'
<?php

    namespace
    {
        use const SymbolX\SymbolY as SymbolZ;
        use SymbolT\SymbolU, SymbolV\SymbolW;
    }

    // some other code

    namespace NamespaceD {}

EOD
                ,
            ),

            'From no uses' => array( //---------------------------------------------------------------------------------
                <<<'EOD'
<?php

    namespace NamespaceA;

    // some other code

    namespace NamespaceD;

EOD
                ,
                0,
                'context',
                <<<'EOD'
<?php

    namespace NamespaceX\NamespaceY;

    use const SymbolX\SymbolY as SymbolZ;
    use SymbolT\SymbolU, SymbolV\SymbolW;

    // some other code

    namespace NamespaceD;

EOD
                ,
            ),

            'From no uses (alternate)' => array( //---------------------------------------------------------------------
                <<<'EOD'
<?php

    namespace NamespaceA {}

    // some other code

    namespace NamespaceD {}

EOD
                ,
                0,
                'context',
                <<<'EOD'
<?php

    namespace NamespaceX\NamespaceY {
        use const SymbolX\SymbolY as SymbolZ;
        use SymbolT\SymbolU, SymbolV\SymbolW;
}

    // some other code

    namespace NamespaceD {}

EOD
                ,
            ),

            'To no uses' => array( //-----------------------------------------------------------------------------------
                <<<'EOD'
<?php

    namespace NamespaceA;

    use SymbolA \ SymbolB \ SymbolC as SymbolD ;

    use function SymbolE , SymbolF ;

    // some other code

    namespace NamespaceD;

EOD
                ,
                0,
                'contextNoUse',
                <<<'EOD'
<?php

    namespace NamespaceX\NamespaceY;

    // some other code

    namespace NamespaceD;

EOD
                ,
            ),

            'To no uses (alternate)' => array( //-----------------------------------------------------------------------
                <<<'EOD'
<?php

    namespace NamespaceA
    {
        use SymbolA \ SymbolB \ SymbolC as SymbolD ;

        use function SymbolE , SymbolF ;
    }

    // some other code

    namespace NamespaceD {}

EOD
                ,
                0,
                'contextNoUse',
                <<<'EOD'
<?php

    namespace NamespaceX\NamespaceY
    {
    }

    // some other code

    namespace NamespaceD {}

EOD
                ,
            ),
        );
    }

    /**
     * @dataProvider replaceContextInData
     */
    public function testReplaceContextInStream($source, $index, $context, $expected)
    {
        $this->streamFixture($source);
        $this->writer
            ->replaceContextInStream($this->stream, $this->parsedContexts[$index], $this->$context, $this->path);
        fseek($this->stream, 0);
        $actual = stream_get_contents($this->stream);

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
