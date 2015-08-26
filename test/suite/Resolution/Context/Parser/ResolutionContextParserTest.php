<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Context\Parser;

use Eloquent\Cosmos\Resolution\Context\Factory\ResolutionContextFactory;
use Eloquent\Cosmos\Resolution\Context\Parser\Element\ParsedResolutionContextInterface;
use Eloquent\Cosmos\Resolution\SymbolResolver;
use Eloquent\Cosmos\Symbol\Factory\SymbolFactory;
use Eloquent\Cosmos\Symbol\Normalizer\SymbolNormalizer;
use Eloquent\Cosmos\UseStatement\Factory\UseStatementFactory;
use Eloquent\Liberator\Liberator;
use Icecave\Isolator\Isolator;
use Phake;
use PHPUnit_Framework_TestCase;

class ResolutionContextParserTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->symbolFactory = new SymbolFactory();
        $this->symbolResolver = new SymbolResolver();
        $this->symbolNormalizer = new SymbolNormalizer();
        $this->useStatementFactory = new UseStatementFactory();
        $this->contextFactory = new ResolutionContextFactory();
        $this->tokenNormalizer = new TokenNormalizer();
        $this->isolator = Phake::mock(Isolator::className());
        Phake::when($this->isolator)->defined('T_TRAIT')->thenReturn(false);
        $this->parser = new ResolutionContextParser(
            $this->symbolFactory,
            $this->symbolResolver,
            $this->symbolNormalizer,
            $this->useStatementFactory,
            $this->contextFactory,
            $this->tokenNormalizer,
            $this->isolator
        );
    }

    public function testConstructor()
    {
        $this->assertSame($this->symbolFactory, $this->parser->symbolFactory());
        $this->assertSame($this->symbolResolver, $this->parser->symbolResolver());
        $this->assertSame($this->symbolNormalizer, $this->parser->symbolNormalizer());
        $this->assertSame($this->useStatementFactory, $this->parser->useStatementFactory());
        $this->assertSame($this->contextFactory, $this->parser->contextFactory());
        $this->assertSame($this->tokenNormalizer, $this->parser->tokenNormalizer());
    }

    public function testConstructorDefaults()
    {
        $this->parser = new ResolutionContextParser();

        $this->assertSame(SymbolFactory::instance(), $this->parser->symbolFactory());
        $this->assertSame(SymbolResolver::instance(), $this->parser->symbolResolver());
        $this->assertSame(SymbolNormalizer::instance(), $this->parser->symbolNormalizer());
        $this->assertSame(UseStatementFactory::instance(), $this->parser->useStatementFactory());
        $this->assertSame(ResolutionContextFactory::instance(), $this->parser->contextFactory());
        $this->assertSame(TokenNormalizer::instance(), $this->parser->tokenNormalizer());
    }

    public function testConstructorTraitSupport()
    {
        Phake::when($this->isolator)->defined('T_TRAIT')->thenReturn(true);
        Phake::when($this->isolator)->constant('T_TRAIT')->thenReturn(111);
        $this->parser = new ResolutionContextParser(null, null, null, null, null, null, $this->isolator);

        $this->assertSame(111, Liberator::liberate($this->parser)->traitTokenType);
    }

    public function testRegularNamespaces()
    {
        $source = <<<'EOD'
Non-PHP content.

<?php // some comment

    declare ( ticks = 1 ) ;

    namespace NamespaceA \ NamespaceB ;

    use ClassF ;

    use ClassG as ClassH ;

    use NamespaceD \ ClassI ;

    use NamespaceE \ ClassJ as ClassK , NamespaceF \ NamespaceG \ ClassL ;

    $object = new namespace \ ClassA ;

    interface InterfaceA
    {
        public function functionA ( ) ;
    }

    interface InterfaceB
    {
        public function functionB ( ) ;
        public function functionC ( ) ;
    }

    interface InterfaceC extends InterfaceA , InterfaceB
    {
    }

    $object = new namespace \ ClassA ;

    class ClassB
    {
    }

    class ClassC implements InterfaceA
    {
        public function functionA()
        {
        }
    }

    class ClassD implements InterfaceA , InterfaceB
    {
        public function functionA()
        {
        }

        public function functionB()
        {
        }

        public function functionC()
        {
        }
    }

    function FunctionA(ClassA $a, ClassB $b = null, ClassC $C = null)
    {
    }

    function FunctionB()
    {
    }

    const CONSTANT_A = 'CONSTANT_A_VALUE';
    const CONSTANT_B = CONSTANT_C;

    $object = new namespace \ ClassA ;

    namespace NamespaceC ;

    use ClassM ;

    use ClassN ;

    class ClassE
    {
    }

    interface InterfaceD
    {
    }

EOD;
        $expected = <<<'EOD'
// Context at position (7, 5), offset 74, size 188:

namespace NamespaceA\NamespaceB;

use ClassF; // at position (9, 5), offset 115, size 12
use ClassG as ClassH; // at position (11, 5), offset 133, size 22
use NamespaceD\ClassI; // at position (13, 5), offset 161, size 25
use NamespaceE\ClassJ as ClassK, NamespaceF\NamespaceG\ClassL; // at position (15, 5), offset 192, size 70

interface \NamespaceA\NamespaceB\InterfaceA; // at position (19, 5), offset 308, size 72
interface \NamespaceA\NamespaceB\InterfaceB; // at position (24, 5), offset 386, size 112
interface \NamespaceA\NamespaceB\InterfaceC; // at position (30, 5), offset 504, size 64
class \NamespaceA\NamespaceB\ClassB; // at position (36, 5), offset 614, size 24
class \NamespaceA\NamespaceB\ClassC; // at position (40, 5), offset 644, size 102
class \NamespaceA\NamespaceB\ClassD; // at position (47, 5), offset 752, size 229
function \NamespaceA\NamespaceB\FunctionA; // at position (62, 5), offset 987, size 77
function \NamespaceA\NamespaceB\FunctionB; // at position (66, 5), offset 1070, size 32

// Context at position (75, 5), offset 1227, size 58:

namespace NamespaceC;

use ClassM; // at position (77, 5), offset 1255, size 12
use ClassN; // at position (79, 5), offset 1273, size 12

class \NamespaceC\ClassE; // at position (81, 5), offset 1291, size 24
interface \NamespaceC\InterfaceD; // at position (85, 5), offset 1321, size 32

EOD;
        $expectedTokens = <<<'EOD'
namespace NamespaceA \ NamespaceB ;

    use ClassF ;

    use ClassG as ClassH ;

    use NamespaceD \ ClassI ;

    use NamespaceE \ ClassJ as ClassK , NamespaceF \ NamespaceG \ ClassL ;

namespace NamespaceC ;

    use ClassM ;

    use ClassN ;

EOD;
        $actual = $this->parser->parseSource($source);
        $actualTokens = $this->renderContextsTokens($actual);

        $this->assertSame($expected, $this->renderContexts($actual));
        $this->assertSame($expectedTokens, $actualTokens);
    }

    public function testAlternateNamespaces()
    {
        $source = <<<'EOD'
Non-PHP content.

<?php // some comment

    declare ( ticks = 1 ) ;

    namespace NamespaceA \ NamespaceB
    {
        use ClassF ;

        use ClassG as ClassH ;

        use NamespaceD \ ClassI ;

        use NamespaceE \ ClassJ as ClassK , NamespaceF \ NamespaceG \ ClassL ;

        $object = new namespace \ ClassA ;

        interface InterfaceA
        {
            public function functionA ( ) ;
        }

        interface InterfaceB
        {
            public function functionB ( ) ;
            public function functionC ( ) ;
        }

        interface InterfaceC extends InterfaceA , InterfaceB
        {
        }

        $object = new namespace \ ClassA ;

        class ClassB
        {
        }

        class ClassC implements InterfaceA
        {
            public function functionA()
            {
            }
        }

        class ClassD implements InterfaceA , InterfaceB
        {
            public function functionA()
            {
            }

            public function functionB()
            {
            }

            public function functionC()
            {
            }
        }

        function FunctionA(ClassA $a, ClassB $b = null, ClassC $C = null)
        {
        }

        function FunctionB()
        {
        }

        const CONSTANT_A = 'CONSTANT_A_VALUE';
        const CONSTANT_B = CONSTANT_C;

        $object = new namespace \ ClassA ;
    }

    namespace NamespaceC
    {
        use ClassM ;

        use ClassN ;

        class ClassE
        {
        }

        interface InterfaceD
        {
        }

        $object = new namespace \ ClassA ;
    }

    namespace
    {
        use ClassO ;

        use ClassP ;

        $object = new namespace \ ClassA ;

        class ClassQ
        {
        }

        interface InterfaceE
        {
        }

        function FunctionC()
        {
        }

        const CONSTANT_D = 'CONSTANT_D_VALUE';
    }

EOD;
        $expected = <<<'EOD'
// Context at position (7, 5), offset 74, size 207:

namespace NamespaceA\NamespaceB;

use ClassF; // at position (9, 9), offset 122, size 12
use ClassG as ClassH; // at position (11, 9), offset 144, size 22
use NamespaceD\ClassI; // at position (13, 9), offset 176, size 25
use NamespaceE\ClassJ as ClassK, NamespaceF\NamespaceG\ClassL; // at position (15, 9), offset 211, size 70

interface \NamespaceA\NamespaceB\InterfaceA; // at position (19, 9), offset 335, size 84
interface \NamespaceA\NamespaceB\InterfaceB; // at position (24, 9), offset 429, size 128
interface \NamespaceA\NamespaceB\InterfaceC; // at position (30, 9), offset 567, size 72
class \NamespaceA\NamespaceB\ClassB; // at position (36, 9), offset 693, size 32
class \NamespaceA\NamespaceB\ClassC; // at position (40, 9), offset 735, size 122
class \NamespaceA\NamespaceB\ClassD; // at position (47, 9), offset 867, size 273
function \NamespaceA\NamespaceB\FunctionA; // at position (62, 9), offset 1150, size 85
function \NamespaceA\NamespaceB\FunctionB; // at position (66, 9), offset 1245, size 40

// Context at position (76, 5), offset 1428, size 69:

namespace NamespaceC;

use ClassM; // at position (78, 9), offset 1463, size 12
use ClassN; // at position (80, 9), offset 1485, size 12

class \NamespaceC\ClassE; // at position (82, 9), offset 1507, size 32
interface \NamespaceC\InterfaceD; // at position (86, 9), offset 1549, size 40

// Context at position (93, 5), offset 1645, size 58:

use ClassO; // at position (95, 9), offset 1669, size 12
use ClassP; // at position (97, 9), offset 1691, size 12

class \ClassQ; // at position (101, 9), offset 1757, size 32
interface \InterfaceE; // at position (105, 9), offset 1799, size 40
function \FunctionC; // at position (109, 9), offset 1849, size 40

EOD;
        $expectedTokens = <<<'EOD'
namespace NamespaceA \ NamespaceB
    {
        use ClassF ;

        use ClassG as ClassH ;

        use NamespaceD \ ClassI ;

        use NamespaceE \ ClassJ as ClassK , NamespaceF \ NamespaceG \ ClassL ;

namespace NamespaceC
    {
        use ClassM ;

        use ClassN ;

namespace
    {
        use ClassO ;

        use ClassP ;

EOD;
        $actual = $this->parser->parseSource($source);
        $actualTokens = $this->renderContextsTokens($actual);

        $this->assertSame($expected, $this->renderContexts($actual));
        $this->assertSame($expectedTokens, $actualTokens);
    }

    public function testNoNamespace()
    {
        $source = <<<'EOD'
Non-PHP content.

<?php // some comment

    declare ( ticks = 1 ) ;

    use ClassF ;

    use ClassG as ClassH ;

    use NamespaceD \ ClassI ;

    use NamespaceE \ ClassJ as ClassK ;

    use NamespaceF \ NamespaceG \ ClassL ;

    $object = new namespace \ ClassA ;

    interface InterfaceA
    {
        public function functionA ( ) ;
    }

    interface InterfaceB
    {
        public function functionB ( ) ;
        public function functionC ( ) ;
    }

    interface InterfaceC extends InterfaceA , InterfaceB
    {
    }

    class ClassB
    {
    }

    class ClassC implements InterfaceA
    {
        public function functionA()
        {
        }
    }

    class ClassD implements InterfaceA , InterfaceB
    {
        public function functionA()
        {
        }

        public function functionB()
        {
        }

        public function functionC()
        {
        }
    }

    namespace NamespaceC ;

    use ClassM ;

    use ClassN ;

    class ClassE
    {
    }

    interface InterfaceD
    {
    }

EOD;
        $expected = <<<'EOD'
// Context at position (7, 5), offset 74, size 156:

use ClassF; // at position (7, 5), offset 74, size 12
use ClassG as ClassH; // at position (9, 5), offset 92, size 22
use NamespaceD\ClassI; // at position (11, 5), offset 120, size 25
use NamespaceE\ClassJ as ClassK; // at position (13, 5), offset 151, size 35
use NamespaceF\NamespaceG\ClassL; // at position (15, 5), offset 192, size 38

interface \InterfaceA; // at position (19, 5), offset 276, size 72
interface \InterfaceB; // at position (24, 5), offset 354, size 112
interface \InterfaceC; // at position (30, 5), offset 472, size 64
class \ClassB; // at position (34, 5), offset 542, size 24
class \ClassC; // at position (38, 5), offset 572, size 102
class \ClassD; // at position (45, 5), offset 680, size 229

// Context at position (60, 5), offset 915, size 58:

namespace NamespaceC;

use ClassM; // at position (62, 5), offset 943, size 12
use ClassN; // at position (64, 5), offset 961, size 12

class \NamespaceC\ClassE; // at position (66, 5), offset 979, size 24
interface \NamespaceC\InterfaceD; // at position (70, 5), offset 1009, size 32

EOD;
        $expectedTokens = <<<'EOD'
use ClassF ;

    use ClassG as ClassH ;

    use NamespaceD \ ClassI ;

    use NamespaceE \ ClassJ as ClassK ;

    use NamespaceF \ NamespaceG \ ClassL ;

namespace NamespaceC ;

    use ClassM ;

    use ClassN ;

EOD;
        $actual = $this->parser->parseSource($source);
        $actualTokens = $this->renderContextsTokens($actual);

        $this->assertSame($expected, $this->renderContexts($actual));
        $this->assertSame($expectedTokens, $actualTokens);
    }

    public function testNoUseStatements()
    {
        $source = <<<'EOD'
Non-PHP content.

<?php // some comment

    declare ( ticks = 1 ) ;

    namespace NamespaceA \ NamespaceB ;

    $object = new namespace \ ClassA ;

    interface InterfaceA
    {
        public function functionA ( ) ;
    }

    interface InterfaceB
    {
        public function functionB ( ) ;
        public function functionC ( ) ;
    }

    interface InterfaceC extends InterfaceA , InterfaceB
    {
    }

    class ClassB
    {
    }

    class ClassC implements InterfaceA
    {
        public function functionA()
        {
        }
    }

    class ClassD implements InterfaceA , InterfaceB
    {
        public function functionA()
        {
        }

        public function functionB()
        {
        }

        public function functionC()
        {
        }
    }

EOD;
        $expected = <<<'EOD'
// Context at position (7, 5), offset 74, size 35:

namespace NamespaceA\NamespaceB;

interface \NamespaceA\NamespaceB\InterfaceA; // at position (11, 5), offset 155, size 72
interface \NamespaceA\NamespaceB\InterfaceB; // at position (16, 5), offset 233, size 112
interface \NamespaceA\NamespaceB\InterfaceC; // at position (22, 5), offset 351, size 64
class \NamespaceA\NamespaceB\ClassB; // at position (26, 5), offset 421, size 24
class \NamespaceA\NamespaceB\ClassC; // at position (30, 5), offset 451, size 102
class \NamespaceA\NamespaceB\ClassD; // at position (37, 5), offset 559, size 229

EOD;
        $expectedTokens = <<<'EOD'
namespace NamespaceA \ NamespaceB ;

EOD;
        $actual = $this->parser->parseSource($source);
        $actualTokens = $this->renderContextsTokens($actual);

        $this->assertSame($expected, $this->renderContexts($actual));
        $this->assertSame($expectedTokens, $actualTokens);
    }

    public function testNoNamespaceOrUseStatements()
    {
        $source = <<<'EOD'
Non-PHP content.

<?php // some comment

    declare ( ticks = 1 ) ;

    $object = new namespace \ ClassA ;

    interface InterfaceA
    {
        public function functionA ( ) ;
    }

    interface InterfaceB
    {
        public function functionB ( ) ;
        public function functionC ( ) ;
    }

    interface InterfaceC extends InterfaceA , InterfaceB
    {
    }

    class ClassB
    {
    }

    class ClassC implements InterfaceA
    {
        public function functionA()
        {
        }
    }

    class ClassD implements InterfaceA , InterfaceB
    {
        public function functionA()
        {
        }

        public function functionB()
        {
        }

        public function functionC()
        {
        }
    }

    namespace NamespaceC ;

    use ClassM ;

    use ClassN ;

    class ClassE
    {
    }

    interface InterfaceD
    {
    }

EOD;
        $expected = <<<'EOD'
// Context at position (3, 7), offset 24, size 0:

interface \InterfaceA; // at position (9, 5), offset 114, size 72
interface \InterfaceB; // at position (14, 5), offset 192, size 112
interface \InterfaceC; // at position (20, 5), offset 310, size 64
class \ClassB; // at position (24, 5), offset 380, size 24
class \ClassC; // at position (28, 5), offset 410, size 102
class \ClassD; // at position (35, 5), offset 518, size 229

// Context at position (50, 5), offset 753, size 58:

namespace NamespaceC;

use ClassM; // at position (52, 5), offset 781, size 12
use ClassN; // at position (54, 5), offset 799, size 12

class \NamespaceC\ClassE; // at position (56, 5), offset 817, size 24
interface \NamespaceC\InterfaceD; // at position (60, 5), offset 847, size 32

EOD;
        $expectedTokens = <<<'EOD'


namespace NamespaceC ;

    use ClassM ;

    use ClassN ;

EOD;
        $actual = $this->parser->parseSource($source);
        $actualTokens = $this->renderContextsTokens($actual);

        $this->assertSame($expected, $this->renderContexts($actual));
        $this->assertSame($expectedTokens, $actualTokens);
    }

    public function testNoClasses()
    {
        $source = <<<'EOD'
Non-PHP content.

<?php // some comment

    declare ( ticks = 1 ) ;

    namespace NamespaceA \ NamespaceB
    {
        use ClassF ;

        use ClassG as ClassH ;

        use NamespaceD \ ClassI ;

        use NamespaceE \ ClassJ as ClassK ;

        use NamespaceF \ NamespaceG \ ClassL ;

        $object = new namespace \ ClassA ;
    }

    namespace NamespaceC
    {
        use ClassM ;

        use ClassN ;
    }

    namespace
    {
        use ClassO ;

        use ClassP ;
    }

EOD;
        $expected = <<<'EOD'
// Context at position (7, 5), offset 74, size 220:

namespace NamespaceA\NamespaceB;

use ClassF; // at position (9, 9), offset 122, size 12
use ClassG as ClassH; // at position (11, 9), offset 144, size 22
use NamespaceD\ClassI; // at position (13, 9), offset 176, size 25
use NamespaceE\ClassJ as ClassK; // at position (15, 9), offset 211, size 35
use NamespaceF\NamespaceG\ClassL; // at position (17, 9), offset 256, size 38

// Context at position (22, 5), offset 350, size 69:

namespace NamespaceC;

use ClassM; // at position (24, 9), offset 385, size 12
use ClassN; // at position (26, 9), offset 407, size 12

// Context at position (29, 5), offset 431, size 58:

use ClassO; // at position (31, 9), offset 455, size 12
use ClassP; // at position (33, 9), offset 477, size 12

EOD;
        $expectedTokens = <<<'EOD'
namespace NamespaceA \ NamespaceB
    {
        use ClassF ;

        use ClassG as ClassH ;

        use NamespaceD \ ClassI ;

        use NamespaceE \ ClassJ as ClassK ;

        use NamespaceF \ NamespaceG \ ClassL ;

namespace NamespaceC
    {
        use ClassM ;

        use ClassN ;

namespace
    {
        use ClassO ;

        use ClassP ;

EOD;
        $actual = $this->parser->parseSource($source);
        $actualTokens = $this->renderContextsTokens($actual);

        $this->assertSame($expected, $this->renderContexts($actual));
        $this->assertSame($expectedTokens, $actualTokens);
    }

    public function testEmptySource()
    {
        $source = <<<'EOD'
Non-PHP content.

EOD;
        $expected = <<<'EOD'
// Context at position (0, 0), offset 0, size 0:

EOD;
        $expectedTokens = <<<'EOD'


EOD;
        $actual = $this->parser->parseSource($source);
        $actualTokens = $this->renderContextsTokens($actual);

        $this->assertSame($expected, $this->renderContexts($actual));
        $this->assertSame($expectedTokens, $actualTokens);
    }

    public function testEmptySourceWithOpenTag()
    {
        $source = <<<'EOD'
Non-PHP content.

<?php // some comment

EOD;
        $expected = <<<'EOD'
// Context at position (3, 7), offset 24, size 0:

EOD;
        $expectedTokens = <<<'EOD'


EOD;
        $actual = $this->parser->parseSource($source);
        $actualTokens = $this->renderContextsTokens($actual);

        $this->assertSame($expected, $this->renderContexts($actual));
        $this->assertSame($expectedTokens, $actualTokens);
    }

    public function testEmptyAlternate()
    {
        $source = <<<'EOD'
Non-PHP content.

<?php // some comment

    namespace
    {
    }

EOD;
        $expected = <<<'EOD'
// Context at position (5, 5), offset 45, size 15:

EOD;
        $expectedTokens = <<<'EOD'
namespace
    {

EOD;
        $actual = $this->parser->parseSource($source);
        $actualTokens = $this->renderContextsTokens($actual);

        $this->assertSame($expected, $this->renderContexts($actual));
        $this->assertSame($expectedTokens, $actualTokens);
    }

    public function testTraitSupport()
    {
        $this->parser = new ResolutionContextParser();
        $source = <<<'EOD'
Non-PHP content.

<?php // some comment

    declare ( ticks = 1 ) ;

    namespace NamespaceA \ NamespaceB ;

    use ClassF ;

    use ClassG as ClassH ;

    use NamespaceD \ ClassI ;

    use NamespaceE \ ClassJ as ClassK ;

    use NamespaceF \ NamespaceG \ ClassL ;

    $object = new namespace \ ClassA ;

    interface InterfaceA
    {
        public function functionA ( ) ;
    }

    interface InterfaceB
    {
        public function functionB ( ) ;
        public function functionC ( ) ;
    }

    interface InterfaceC extends InterfaceA , InterfaceB
    {
    }

    trait TraitA
    {
    }

    trait TraitB
    {
    }

    trait TraitC
    {
        use TraitA ;

        use TraitB ;
    }

    class ClassB
    {
    }

    class ClassC implements InterfaceA
    {
        public function functionA()
        {
        }
    }

    class ClassD implements InterfaceA , InterfaceB
    {
        use TraitA ;

        use TraitB ;

        public function functionA()
        {
        }

        public function functionB()
        {
        }

        public function functionC()
        {
        }
    }

EOD;
        $expected = <<<'EOD'
// Context at position (7, 5), offset 74, size 197:

namespace NamespaceA\NamespaceB;

use ClassF; // at position (9, 5), offset 115, size 12
use ClassG as ClassH; // at position (11, 5), offset 133, size 22
use NamespaceD\ClassI; // at position (13, 5), offset 161, size 25
use NamespaceE\ClassJ as ClassK; // at position (15, 5), offset 192, size 35
use NamespaceF\NamespaceG\ClassL; // at position (17, 5), offset 233, size 38

interface \NamespaceA\NamespaceB\InterfaceA; // at position (21, 5), offset 317, size 72
interface \NamespaceA\NamespaceB\InterfaceB; // at position (26, 5), offset 395, size 112
interface \NamespaceA\NamespaceB\InterfaceC; // at position (32, 5), offset 513, size 64
trait \NamespaceA\NamespaceB\TraitA; // at position (36, 5), offset 583, size 24
trait \NamespaceA\NamespaceB\TraitB; // at position (40, 5), offset 613, size 24
trait \NamespaceA\NamespaceB\TraitC; // at position (44, 5), offset 643, size 67
class \NamespaceA\NamespaceB\ClassB; // at position (51, 5), offset 716, size 24
class \NamespaceA\NamespaceB\ClassC; // at position (55, 5), offset 746, size 102
class \NamespaceA\NamespaceB\ClassD; // at position (62, 5), offset 854, size 273

EOD;
        $expectedTokens = <<<'EOD'
namespace NamespaceA \ NamespaceB ;

    use ClassF ;

    use ClassG as ClassH ;

    use NamespaceD \ ClassI ;

    use NamespaceE \ ClassJ as ClassK ;

    use NamespaceF \ NamespaceG \ ClassL ;

EOD;
        $actual = $this->parser->parseSource($source);
        $actualTokens = $this->renderContextsTokens($actual);

        $this->assertSame($expected, $this->renderContexts($actual));
        $this->assertSame($expectedTokens, $actualTokens);
    }

    public function testNamespaceAndTraitOnly()
    {
        $this->parser = new ResolutionContextParser();
        $source = <<<'EOD'
<?php

    namespace NamespaceA;

    trait TraitA
    {
    }

EOD;
        $expected = <<<'EOD'
// Context at position (3, 5), offset 11, size 21:

namespace NamespaceA;

trait \NamespaceA\TraitA; // at position (5, 5), offset 38, size 24

EOD;
        $expectedTokens = <<<'EOD'
namespace NamespaceA;

EOD;
        $actual = $this->parser->parseSource($source);
        $actualTokens = $this->renderContextsTokens($actual);

        $this->assertSame($expected, $this->renderContexts($actual));
        $this->assertSame($expectedTokens, $actualTokens);
    }

    public function testNamespaceAndFunctionOnly()
    {
        $this->parser = new ResolutionContextParser();
        $source = <<<'EOD'
<?php

    namespace NamespaceA;

    function FunctionA
    {
    }

EOD;
        $expected = <<<'EOD'
// Context at position (3, 5), offset 11, size 21:

namespace NamespaceA;

function \NamespaceA\FunctionA; // at position (5, 5), offset 38, size 30

EOD;
        $expectedTokens = <<<'EOD'
namespace NamespaceA;

EOD;
        $actual = $this->parser->parseSource($source);
        $actualTokens = $this->renderContextsTokens($actual);

        $this->assertSame($expected, $this->renderContexts($actual));
        $this->assertSame($expectedTokens, $actualTokens);
    }

    public function testUseStatementTypes()
    {
        $this->parser = new ResolutionContextParser();
        $source = <<<'EOD'
<?php

    use ClassF ;

    use ClassG as ClassH ;

    use NamespaceD \ ClassI ;

    use NamespaceE \ ClassJ as ClassK ;

    use NamespaceF \ NamespaceG \ ClassL ;

    use function FunctionA ;

    use function FunctionB as FunctionC ;

    use function NamespaceG \ FunctionD ;

    use function NamespaceH \ FunctionE as FunctionF ;

    use const CONSTANT_A ;

    use const CONSTANT_B as CONSTANT_C ;

    use const NamespaceI \ CONSTANT_D ;

    use const NamespaceJ \ CONSTANT_E as CONSTANT_F ;

EOD;
        $expected = <<<'EOD'
// Context at position (3, 5), offset 11, size 494:

use ClassF; // at position (3, 5), offset 11, size 12
use ClassG as ClassH; // at position (5, 5), offset 29, size 22
use NamespaceD\ClassI; // at position (7, 5), offset 57, size 25
use NamespaceE\ClassJ as ClassK; // at position (9, 5), offset 88, size 35
use NamespaceF\NamespaceG\ClassL; // at position (11, 5), offset 129, size 38
use function FunctionA; // at position (13, 5), offset 173, size 24
use function FunctionB as FunctionC; // at position (15, 5), offset 203, size 37
use function NamespaceG\FunctionD; // at position (17, 5), offset 246, size 37
use function NamespaceH\FunctionE as FunctionF; // at position (19, 5), offset 289, size 50
use const CONSTANT_A; // at position (21, 5), offset 345, size 22
use const CONSTANT_B as CONSTANT_C; // at position (23, 5), offset 373, size 36
use const NamespaceI\CONSTANT_D; // at position (25, 5), offset 415, size 35
use const NamespaceJ\CONSTANT_E as CONSTANT_F; // at position (27, 5), offset 456, size 49

EOD;
        $expectedTokens = <<<'EOD'
use ClassF ;

    use ClassG as ClassH ;

    use NamespaceD \ ClassI ;

    use NamespaceE \ ClassJ as ClassK ;

    use NamespaceF \ NamespaceG \ ClassL ;

    use function FunctionA ;

    use function FunctionB as FunctionC ;

    use function NamespaceG \ FunctionD ;

    use function NamespaceH \ FunctionE as FunctionF ;

    use const CONSTANT_A ;

    use const CONSTANT_B as CONSTANT_C ;

    use const NamespaceI \ CONSTANT_D ;

    use const NamespaceJ \ CONSTANT_E as CONSTANT_F ;

EOD;
        $actual = $this->parser->parseSource($source);
        $actualTokens = $this->renderContextsTokens($actual);

        $this->assertSame($expected, $this->renderContexts($actual));
        $this->assertSame($expectedTokens, $actualTokens);
    }

    public function testClassOnFirstLine()
    {
        $source = <<<'EOD'
<?php
class ClassA
{
    public function methodA()
    {
    }
}
EOD;
        $expected = <<<'EOD'
// Context at position (2, 1), offset 6, size 0:

class \ClassA; // at position (2, 1), offset 6, size 58

EOD;
        $expectedTokens = <<<'EOD'


EOD;
        $actual = $this->parser->parseSource($source);
        $actualTokens = $this->renderContextsTokens($actual);

        $this->assertSame($expected, $this->renderContexts($actual));
        $this->assertSame($expectedTokens, $actualTokens);
    }

    public function testInstance()
    {
        $class = get_class($this->parser);
        $liberatedClass = Liberator::liberateClass($class);
        $liberatedClass->instance = null;
        $actual = $class::instance();

        $this->assertInstanceOf($class, $actual);
        $this->assertSame($actual, $class::instance());
    }

    protected function renderContexts(array $contexts)
    {
        $rendered = '';
        foreach ($contexts as $context) {
            if ('' !== $rendered) {
                $rendered .= "\n";
            }

            $rendered .= $this->renderContext($context);
        }

        return $rendered;
    }

    protected function renderContext(ParsedResolutionContextInterface $context)
    {
        $rendered = sprintf(
            "// Context at position (%d, %d), offset %d, size %d:\n",
            $context->position()->line(),
            $context->position()->column(),
            $context->offset(),
            $context->size()
        );

        if (!$context->primaryNamespace()->isRoot()) {
            $rendered .= sprintf("\nnamespace %s;\n", $context->primaryNamespace()->toRelative()->string());
        }

        if (count($context->useStatements()) > 0) {
            $rendered .= "\n";
        }

        foreach ($context->useStatements() as $useStatement) {
            $rendered .= sprintf(
                "%s; // at position (%d, %d), offset %d, size %d\n",
                $useStatement,
                $useStatement->position()->line(),
                $useStatement->position()->column(),
                $useStatement->offset(),
                $useStatement->size()
            );
        }

        if (count($context->symbols()) > 0) {
            $rendered .= "\n";
        }

        foreach ($context->symbols() as $symbol) {
            $rendered .= sprintf(
                "%s %s; // at position (%d, %d), offset %d, size %d\n",
                $symbol->type()->value(),
                $symbol->symbol()->string(),
                $symbol->position()->line(),
                $symbol->position()->column(),
                $symbol->offset(),
                $symbol->size()
            );
        }

        return $rendered;
    }

    protected function renderContextsTokens(array $contexts)
    {
        $rendered = '';
        foreach ($contexts as $context) {
            if ('' !== $rendered) {
                $rendered .= "\n";
            }

            $rendered .= $this->renderTokens($context->tokens()) . "\n";
        }

        return $rendered;
    }

    protected function renderTokens(array $tokens)
    {
        return implode(
            array_map(
                function ($token) {
                    return $token[1];
                },
                $tokens
            )
        );
    }
}
