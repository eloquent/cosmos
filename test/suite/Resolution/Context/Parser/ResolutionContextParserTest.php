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
// Context at position (5, 5):

namespace NamespaceA\NamespaceB;

use ClassF; // at position (7, 5)
use ClassG as ClassH; // at position (9, 5)
use NamespaceD\ClassI; // at position (11, 5)
use NamespaceE\ClassJ as ClassK, NamespaceF\NamespaceG\ClassL; // at position (13, 5)

interface \NamespaceA\NamespaceB\InterfaceA; // at position (17, 5)
interface \NamespaceA\NamespaceB\InterfaceB; // at position (22, 5)
interface \NamespaceA\NamespaceB\InterfaceC; // at position (28, 5)
class \NamespaceA\NamespaceB\ClassB; // at position (34, 5)
class \NamespaceA\NamespaceB\ClassC; // at position (38, 5)
class \NamespaceA\NamespaceB\ClassD; // at position (45, 5)
function \NamespaceA\NamespaceB\FunctionA; // at position (60, 5)
function \NamespaceA\NamespaceB\FunctionB; // at position (64, 5)
const \NamespaceA\NamespaceB\CONSTANT_A; // at position (68, 5)
const \NamespaceA\NamespaceB\CONSTANT_B; // at position (69, 5)

// Context at position (73, 5):

namespace NamespaceC;

use ClassM; // at position (75, 5)
use ClassN; // at position (77, 5)

class \NamespaceC\ClassE; // at position (79, 5)
interface \NamespaceC\InterfaceD; // at position (83, 5)

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
// Context at position (5, 5):

namespace NamespaceA\NamespaceB;

use ClassF; // at position (7, 9)
use ClassG as ClassH; // at position (9, 9)
use NamespaceD\ClassI; // at position (11, 9)
use NamespaceE\ClassJ as ClassK, NamespaceF\NamespaceG\ClassL; // at position (13, 9)

interface \NamespaceA\NamespaceB\InterfaceA; // at position (17, 9)
interface \NamespaceA\NamespaceB\InterfaceB; // at position (22, 9)
interface \NamespaceA\NamespaceB\InterfaceC; // at position (28, 9)
class \NamespaceA\NamespaceB\ClassB; // at position (34, 9)
class \NamespaceA\NamespaceB\ClassC; // at position (38, 9)
class \NamespaceA\NamespaceB\ClassD; // at position (45, 9)
function \NamespaceA\NamespaceB\FunctionA; // at position (60, 9)
function \NamespaceA\NamespaceB\FunctionB; // at position (64, 9)
const \NamespaceA\NamespaceB\CONSTANT_A; // at position (68, 9)
const \NamespaceA\NamespaceB\CONSTANT_B; // at position (69, 9)

// Context at position (74, 5):

namespace NamespaceC;

use ClassM; // at position (76, 9)
use ClassN; // at position (78, 9)

class \NamespaceC\ClassE; // at position (80, 9)
interface \NamespaceC\InterfaceD; // at position (84, 9)

// Context at position (91, 5):

use ClassO; // at position (93, 9)
use ClassP; // at position (95, 9)

class \ClassQ; // at position (99, 9)
interface \InterfaceE; // at position (103, 9)
function \FunctionC; // at position (107, 9)
const \CONSTANT_D; // at position (111, 9)

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
// Context at position (1, 1):

use ClassF; // at position (5, 5)
use ClassG as ClassH; // at position (7, 5)
use NamespaceD\ClassI; // at position (9, 5)
use NamespaceE\ClassJ as ClassK; // at position (11, 5)
use NamespaceF\NamespaceG\ClassL; // at position (13, 5)

interface \InterfaceA; // at position (17, 5)
interface \InterfaceB; // at position (22, 5)
interface \InterfaceC; // at position (28, 5)
class \ClassB; // at position (32, 5)
class \ClassC; // at position (36, 5)
class \ClassD; // at position (43, 5)

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
// Context at position (5, 5):

namespace NamespaceA\NamespaceB;

interface \NamespaceA\NamespaceB\InterfaceA; // at position (9, 5)
interface \NamespaceA\NamespaceB\InterfaceB; // at position (14, 5)
interface \NamespaceA\NamespaceB\InterfaceC; // at position (20, 5)
class \NamespaceA\NamespaceB\ClassB; // at position (24, 5)
class \NamespaceA\NamespaceB\ClassC; // at position (28, 5)
class \NamespaceA\NamespaceB\ClassD; // at position (35, 5)

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
// Context at position (1, 1):

interface \InterfaceA; // at position (7, 5)
interface \InterfaceB; // at position (12, 5)
interface \InterfaceC; // at position (18, 5)
class \ClassB; // at position (22, 5)
class \ClassC; // at position (26, 5)
class \ClassD; // at position (33, 5)

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
// Context at position (5, 5):

namespace NamespaceA\NamespaceB;

use ClassF; // at position (7, 9)
use ClassG as ClassH; // at position (9, 9)
use NamespaceD\ClassI; // at position (11, 9)
use NamespaceE\ClassJ as ClassK; // at position (13, 9)
use NamespaceF\NamespaceG\ClassL; // at position (15, 9)

// Context at position (20, 5):

namespace NamespaceC;

use ClassM; // at position (22, 9)
use ClassN; // at position (24, 9)

// Context at position (27, 5):

use ClassO; // at position (29, 9)
use ClassP; // at position (31, 9)

EOD;
        $actual = $this->parser->parseSource($source);

        $this->assertSame($expected, $this->renderContexts($actual));
    }

    public function testEmptySource()
    {
        $source = '';
        $expected = <<<'EOD'
// Context at position (1, 1):

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
// Context at position (5, 5):

namespace NamespaceA\NamespaceB;

use ClassF; // at position (7, 5)
use ClassG as ClassH; // at position (9, 5)
use NamespaceD\ClassI; // at position (11, 5)
use NamespaceE\ClassJ as ClassK; // at position (13, 5)
use NamespaceF\NamespaceG\ClassL; // at position (15, 5)

interface \NamespaceA\NamespaceB\InterfaceA; // at position (19, 5)
interface \NamespaceA\NamespaceB\InterfaceB; // at position (24, 5)
interface \NamespaceA\NamespaceB\InterfaceC; // at position (30, 5)
trait \NamespaceA\NamespaceB\TraitA; // at position (34, 5)
trait \NamespaceA\NamespaceB\TraitB; // at position (38, 5)
trait \NamespaceA\NamespaceB\TraitC; // at position (42, 5)
class \NamespaceA\NamespaceB\ClassB; // at position (49, 5)
class \NamespaceA\NamespaceB\ClassC; // at position (53, 5)
class \NamespaceA\NamespaceB\ClassD; // at position (60, 5)

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
// Context at position (3, 5):

namespace NamespaceA;

trait \NamespaceA\TraitA; // at position (5, 5)

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
// Context at position (3, 5):

namespace NamespaceA;

function \NamespaceA\FunctionA; // at position (5, 5)

EOD;
        $actual = $this->parser->parseSource($source);

        $this->assertSame($expected, $this->renderContexts($actual));
    }

    public function testNamespaceAndConstantOnly()
    {
        $this->parser = new ResolutionContextParser;
        $source = <<<'EOD'
<?php

    namespace NamespaceA;

    const CONSTANT_A = 'CONSTANT_A_VALUE';

EOD;
        $expected = <<<'EOD'
// Context at position (3, 5):

namespace NamespaceA;

const \NamespaceA\CONSTANT_A; // at position (5, 5)

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
// Context at position (1, 1):

use ClassF; // at position (3, 5)
use ClassG as ClassH; // at position (5, 5)
use NamespaceD\ClassI; // at position (7, 5)
use NamespaceE\ClassJ as ClassK; // at position (9, 5)
use NamespaceF\NamespaceG\ClassL; // at position (11, 5)
use function FunctionA; // at position (13, 5)
use function FunctionB as FunctionC; // at position (15, 5)
use function NamespaceG\FunctionD; // at position (17, 5)
use function NamespaceH\FunctionE as FunctionF; // at position (19, 5)
use const CONSTANT_A; // at position (21, 5)
use const CONSTANT_B as CONSTANT_C; // at position (23, 5)
use const NamespaceI\CONSTANT_D; // at position (25, 5)
use const NamespaceJ\CONSTANT_E as CONSTANT_F; // at position (27, 5)

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
            "// Context at position (%d, %d):\n",
            $context->position()->line(),
            $context->position()->column()
        );

        if (!$context->primaryNamespace()->isRoot()) {
            $rendered .= sprintf("\nnamespace %s;\n", $context->primaryNamespace()->toRelative()->string());
        }

        if (count($context->useStatements()) > 0) {
            $rendered .= "\n";
        }

        foreach ($context->useStatements() as $useStatement) {
            $rendered .= sprintf(
                "%s; // at position (%d, %d)\n",
                $useStatement,
                $useStatement->position()->line(),
                $useStatement->position()->column()
            );
        }

        if (count($context->symbols()) > 0) {
            $rendered .= "\n";
        }

        foreach ($context->symbols() as $symbol) {
            $rendered .= sprintf(
                "%s %s; // at position (%d, %d)\n",
                $symbol->type()->value(),
                $symbol->symbol()->string(),
                $symbol->position()->line(),
                $symbol->position()->column()
            );
        }

        return $rendered;
    }
}
