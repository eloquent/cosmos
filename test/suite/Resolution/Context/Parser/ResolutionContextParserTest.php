<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
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
use Eloquent\Cosmos\UseStatement\UseStatement;
use Eloquent\Liberator\Liberator;
use Icecave\Isolator\Isolator;
use Phake;
use PHPUnit_Framework_TestCase;

class ResolutionContextParserTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->symbolFactory = new SymbolFactory;
        $this->symbolResolver = new SymbolResolver;
        $this->symbolNormalizer = new SymbolNormalizer;
        $this->useStatementFactory = new UseStatementFactory;
        $this->contextFactory = new ResolutionContextFactory;
        $this->tokenNormalizer = new TokenNormalizer;
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
        $this->parser = new ResolutionContextParser;

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
<?php

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
// Context at position (5, 5), offset 41, size 188:

namespace NamespaceA\NamespaceB;

use ClassF; // at position (7, 5), offset 82, size 12
use ClassG as ClassH; // at position (9, 5), offset 100, size 22
use NamespaceD\ClassI; // at position (11, 5), offset 128, size 25
use NamespaceE\ClassJ as ClassK, NamespaceF\NamespaceG\ClassL; // at position (13, 5), offset 159, size 70

interface \NamespaceA\NamespaceB\InterfaceA; // at position (17, 5), offset 275, size 72
interface \NamespaceA\NamespaceB\InterfaceB; // at position (22, 5), offset 353, size 112
interface \NamespaceA\NamespaceB\InterfaceC; // at position (28, 5), offset 471, size 64
class \NamespaceA\NamespaceB\ClassB; // at position (34, 5), offset 581, size 24
class \NamespaceA\NamespaceB\ClassC; // at position (38, 5), offset 611, size 102
class \NamespaceA\NamespaceB\ClassD; // at position (45, 5), offset 719, size 229
function \NamespaceA\NamespaceB\FunctionA; // at position (60, 5), offset 954, size 77
function \NamespaceA\NamespaceB\FunctionB; // at position (64, 5), offset 1037, size 32

// Context at position (73, 5), offset 1194, size 58:

namespace NamespaceC;

use ClassM; // at position (75, 5), offset 1222, size 12
use ClassN; // at position (77, 5), offset 1240, size 12

class \NamespaceC\ClassE; // at position (79, 5), offset 1258, size 24
interface \NamespaceC\InterfaceD; // at position (83, 5), offset 1288, size 32

EOD;
        $actual = $this->parser->parseSource($source);

        $this->assertSame($expected, $this->renderContexts($actual));
    }

    public function testAlternateNamespaces()
    {
        $source = <<<'EOD'
<?php

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
// Context at position (5, 5), offset 41, size 207:

namespace NamespaceA\NamespaceB;

use ClassF; // at position (7, 9), offset 89, size 12
use ClassG as ClassH; // at position (9, 9), offset 111, size 22
use NamespaceD\ClassI; // at position (11, 9), offset 143, size 25
use NamespaceE\ClassJ as ClassK, NamespaceF\NamespaceG\ClassL; // at position (13, 9), offset 178, size 70

interface \NamespaceA\NamespaceB\InterfaceA; // at position (17, 9), offset 302, size 84
interface \NamespaceA\NamespaceB\InterfaceB; // at position (22, 9), offset 396, size 128
interface \NamespaceA\NamespaceB\InterfaceC; // at position (28, 9), offset 534, size 72
class \NamespaceA\NamespaceB\ClassB; // at position (34, 9), offset 660, size 32
class \NamespaceA\NamespaceB\ClassC; // at position (38, 9), offset 702, size 122
class \NamespaceA\NamespaceB\ClassD; // at position (45, 9), offset 834, size 273
function \NamespaceA\NamespaceB\FunctionA; // at position (60, 9), offset 1117, size 85
function \NamespaceA\NamespaceB\FunctionB; // at position (64, 9), offset 1212, size 40

// Context at position (74, 5), offset 1395, size 69:

namespace NamespaceC;

use ClassM; // at position (76, 9), offset 1430, size 12
use ClassN; // at position (78, 9), offset 1452, size 12

class \NamespaceC\ClassE; // at position (80, 9), offset 1474, size 32
interface \NamespaceC\InterfaceD; // at position (84, 9), offset 1516, size 40

// Context at position (91, 5), offset 1612, size 58:

use ClassO; // at position (93, 9), offset 1636, size 12
use ClassP; // at position (95, 9), offset 1658, size 12

class \ClassQ; // at position (99, 9), offset 1724, size 32
interface \InterfaceE; // at position (103, 9), offset 1766, size 40
function \FunctionC; // at position (107, 9), offset 1816, size 40

EOD;
        $actual = $this->parser->parseSource($source);

        $this->assertSame($expected, $this->renderContexts($actual));
    }

    public function testNoNamespace()
    {
        $source = <<<'EOD'
<?php

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

EOD;
        $expected = <<<'EOD'
// Context at position (1, 1), offset 41, size 156:

use ClassF; // at position (5, 5), offset 41, size 12
use ClassG as ClassH; // at position (7, 5), offset 59, size 22
use NamespaceD\ClassI; // at position (9, 5), offset 87, size 25
use NamespaceE\ClassJ as ClassK; // at position (11, 5), offset 118, size 35
use NamespaceF\NamespaceG\ClassL; // at position (13, 5), offset 159, size 38

interface \InterfaceA; // at position (17, 5), offset 243, size 72
interface \InterfaceB; // at position (22, 5), offset 321, size 112
interface \InterfaceC; // at position (28, 5), offset 439, size 64
class \ClassB; // at position (32, 5), offset 509, size 24
class \ClassC; // at position (36, 5), offset 539, size 102
class \ClassD; // at position (43, 5), offset 647, size 229

EOD;
        $actual = $this->parser->parseSource($source);

        $this->assertSame($expected, $this->renderContexts($actual));
    }

    public function testNoUseStatements()
    {
        $source = <<<'EOD'
<?php

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
// Context at position (5, 5), offset 41, size 35:

namespace NamespaceA\NamespaceB;

interface \NamespaceA\NamespaceB\InterfaceA; // at position (9, 5), offset 122, size 72
interface \NamespaceA\NamespaceB\InterfaceB; // at position (14, 5), offset 200, size 112
interface \NamespaceA\NamespaceB\InterfaceC; // at position (20, 5), offset 318, size 64
class \NamespaceA\NamespaceB\ClassB; // at position (24, 5), offset 388, size 24
class \NamespaceA\NamespaceB\ClassC; // at position (28, 5), offset 418, size 102
class \NamespaceA\NamespaceB\ClassD; // at position (35, 5), offset 526, size 229

EOD;
        $actual = $this->parser->parseSource($source);

        $this->assertSame($expected, $this->renderContexts($actual));
    }

    public function testNoNamespaceOrUseStatements()
    {
        $source = <<<'EOD'
<?php

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

EOD;
        $expected = <<<'EOD'
// Context at position (1, 1), offset 0, size 0:

interface \InterfaceA; // at position (7, 5), offset 81, size 72
interface \InterfaceB; // at position (12, 5), offset 159, size 112
interface \InterfaceC; // at position (18, 5), offset 277, size 64
class \ClassB; // at position (22, 5), offset 347, size 24
class \ClassC; // at position (26, 5), offset 377, size 102
class \ClassD; // at position (33, 5), offset 485, size 229

EOD;
        $actual = $this->parser->parseSource($source);

        $this->assertSame($expected, $this->renderContexts($actual));
    }

    public function testNoClasses()
    {
        $source = <<<'EOD'
<?php

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
// Context at position (5, 5), offset 41, size 220:

namespace NamespaceA\NamespaceB;

use ClassF; // at position (7, 9), offset 89, size 12
use ClassG as ClassH; // at position (9, 9), offset 111, size 22
use NamespaceD\ClassI; // at position (11, 9), offset 143, size 25
use NamespaceE\ClassJ as ClassK; // at position (13, 9), offset 178, size 35
use NamespaceF\NamespaceG\ClassL; // at position (15, 9), offset 223, size 38

// Context at position (20, 5), offset 317, size 69:

namespace NamespaceC;

use ClassM; // at position (22, 9), offset 352, size 12
use ClassN; // at position (24, 9), offset 374, size 12

// Context at position (27, 5), offset 398, size 58:

use ClassO; // at position (29, 9), offset 422, size 12
use ClassP; // at position (31, 9), offset 444, size 12

EOD;
        $actual = $this->parser->parseSource($source);

        $this->assertSame($expected, $this->renderContexts($actual));
    }

    public function testEmptySource()
    {
        $source = '';
        $expected = <<<'EOD'
// Context at position (1, 1), offset 0, size 0:

EOD;
        $actual = $this->parser->parseSource($source);

        $this->assertSame($expected, $this->renderContexts($actual));
    }

    public function testTraitSupport()
    {
        $this->parser = new ResolutionContextParser;
        $source = <<<'EOD'
<?php

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
// Context at position (5, 5), offset 41, size 197:

namespace NamespaceA\NamespaceB;

use ClassF; // at position (7, 5), offset 82, size 12
use ClassG as ClassH; // at position (9, 5), offset 100, size 22
use NamespaceD\ClassI; // at position (11, 5), offset 128, size 25
use NamespaceE\ClassJ as ClassK; // at position (13, 5), offset 159, size 35
use NamespaceF\NamespaceG\ClassL; // at position (15, 5), offset 200, size 38

interface \NamespaceA\NamespaceB\InterfaceA; // at position (19, 5), offset 284, size 72
interface \NamespaceA\NamespaceB\InterfaceB; // at position (24, 5), offset 362, size 112
interface \NamespaceA\NamespaceB\InterfaceC; // at position (30, 5), offset 480, size 64
trait \NamespaceA\NamespaceB\TraitA; // at position (34, 5), offset 550, size 24
trait \NamespaceA\NamespaceB\TraitB; // at position (38, 5), offset 580, size 24
trait \NamespaceA\NamespaceB\TraitC; // at position (42, 5), offset 610, size 67
class \NamespaceA\NamespaceB\ClassB; // at position (49, 5), offset 683, size 24
class \NamespaceA\NamespaceB\ClassC; // at position (53, 5), offset 713, size 102
class \NamespaceA\NamespaceB\ClassD; // at position (60, 5), offset 821, size 273

EOD;
        $actual = $this->parser->parseSource($source);

        $this->assertSame($expected, $this->renderContexts($actual));
    }

    public function testNamespaceAndTraitOnly()
    {
        $this->parser = new ResolutionContextParser;
        $source = <<<'EOD'
<?php

    namespace NamespaceA;

    trait TraitA
    {
    }

EOD;
        $expected = <<<'EOD'
// Context at position (3, 5), offset 12, size 21:

namespace NamespaceA;

trait \NamespaceA\TraitA; // at position (5, 5), offset 39, size 24

EOD;
        $actual = $this->parser->parseSource($source);

        $this->assertSame($expected, $this->renderContexts($actual));
    }

    public function testNamespaceAndFunctionOnly()
    {
        $this->parser = new ResolutionContextParser;
        $source = <<<'EOD'
<?php

    namespace NamespaceA;

    function FunctionA
    {
    }

EOD;
        $expected = <<<'EOD'
// Context at position (3, 5), offset 12, size 21:

namespace NamespaceA;

function \NamespaceA\FunctionA; // at position (5, 5), offset 39, size 30

EOD;
        $actual = $this->parser->parseSource($source);

        $this->assertSame($expected, $this->renderContexts($actual));
    }

    public function testUseStatementTypes()
    {
        $this->parser = new ResolutionContextParser;
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
// Context at position (1, 1), offset 12, size 494:

use ClassF; // at position (3, 5), offset 12, size 12
use ClassG as ClassH; // at position (5, 5), offset 30, size 22
use NamespaceD\ClassI; // at position (7, 5), offset 58, size 25
use NamespaceE\ClassJ as ClassK; // at position (9, 5), offset 89, size 35
use NamespaceF\NamespaceG\ClassL; // at position (11, 5), offset 130, size 38
use function FunctionA; // at position (13, 5), offset 174, size 24
use function FunctionB as FunctionC; // at position (15, 5), offset 204, size 37
use function NamespaceG\FunctionD; // at position (17, 5), offset 247, size 37
use function NamespaceH\FunctionE as FunctionF; // at position (19, 5), offset 290, size 50
use const CONSTANT_A; // at position (21, 5), offset 346, size 22
use const CONSTANT_B as CONSTANT_C; // at position (23, 5), offset 374, size 36
use const NamespaceI\CONSTANT_D; // at position (25, 5), offset 416, size 35
use const NamespaceJ\CONSTANT_E as CONSTANT_F; // at position (27, 5), offset 457, size 49

EOD;
        $actual = $this->parser->parseSource($source);

        $this->assertSame($expected, $this->renderContexts($actual));
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
            $context->startOffset(),
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
                $useStatement->startOffset(),
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
                $symbol->startOffset(),
                $symbol->size()
            );
        }

        return $rendered;
    }
}
