<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Parser;

use Eloquent\Cosmos\ClassName\ClassName;
use Eloquent\Cosmos\Resolution\ResolutionContext;
use Eloquent\Cosmos\UseStatement\UseStatement;
use Eloquent\Liberator\Liberator;
use PHPUnit_Framework_TestCase;

class ResolutionContextParserTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->parser = new ResolutionContextParser;
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

//     public function testParseGlobalNamespaceNoUseStatements()
//     {
//         $source = <<<'EOD'
// <?php
// exit

// ;
// EOD;
//         $expected = array(new ParsedResolutionContext);

//         $this->assertEquals($expected, $this->parser->parseSource($source));
//     }

//     public function testParseGlobalNamespaceSingleClass()
//     {
//         $source = <<<'EOD'
// <?php
// class ClassA {}
// EOD;
//         $contextA = new ResolutionContext;
//         $classNameA = ClassName::fromString('\ClassA');
//         $expected = array(new ParsedResolutionContext($contextA, array($classNameA)));

//         $this->assertEquals($expected, $this->parser->parseSource($source));
//     }

//     public function testParseGlobalNamespaceMultipleTypes()
//     {
//         $source = <<<'EOD'
// <?php
// class ClassA {}
// interface InterfaceA {}
// interface InterfaceB {}
// interface InterfaceC extends InterfaceA, InterfaceB {}
// class ClassB extends ClassA implements InterfaceA, InterfaceB {}
// EOD;
//         $contextA = new ResolutionContext;
//         $classNameA = ClassName::fromString('\ClassA');
//         $classNameB = ClassName::fromString('\InterfaceA');
//         $classNameC = ClassName::fromString('\InterfaceB');
//         $classNameD = ClassName::fromString('\InterfaceC');
//         $classNameE = ClassName::fromString('\ClassB');
//         $classNames = array($classNameA, $classNameB, $classNameC, $classNameD, $classNameE);
//         $expected = array(new ParsedResolutionContext($contextA, $classNames));

//         $this->assertEquals($expected, $this->parser->parseSource($source));
//     }

//     public function testParseTraits()
//     {
//         if (!defined('T_TRAIT')) {
//             $this->markTestSkipped('Requires trait support.');
//         }

//         $source = <<<'EOD'
// <?php
// trait TraitA {}
// trait TraitB {}
// EOD;
//         $contextA = new ResolutionContext;
//         $classNameA = ClassName::fromString('\TraitA');
//         $classNameB = ClassName::fromString('\TraitB');
//         $classNames = array($classNameA, $classNameB);
//         $expected = array(new ParsedResolutionContext($contextA, $classNames));

//         $this->assertEquals($expected, $this->parser->parseSource($source));
//     }

    public function testRegularNamespaces()
    {
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
        $namespaceAB = ClassName::fromString('\NamespaceA\NamespaceB');
        $useF = new UseStatement(ClassName::fromString('\ClassF'));
        $useG = new UseStatement(ClassName::fromString('\ClassG'), ClassName::fromString('ClassH'));
        $useI = new UseStatement(ClassName::fromString('\NamespaceD\ClassI'));
        $useJ = new UseStatement(ClassName::fromString('\NamespaceE\ClassJ'), ClassName::fromString('ClassK'));
        $useL = new UseStatement(ClassName::fromString('\NamespaceF\NamespaceG\ClassL'));
        $useStatementsA = array($useF, $useG, $useI, $useJ, $useL);
        $contextA = new ResolutionContext($namespaceAB, $useStatementsA);
        $interfaceA = ClassName::fromString('\NamespaceA\NamespaceB\InterfaceA');
        $interfaceB = ClassName::fromString('\NamespaceA\NamespaceB\InterfaceB');
        $interfaceC = ClassName::fromString('\NamespaceA\NamespaceB\InterfaceC');
        $classB = ClassName::fromString('\NamespaceA\NamespaceB\ClassB');
        $classC = ClassName::fromString('\NamespaceA\NamespaceB\ClassC');
        $classD = ClassName::fromString('\NamespaceA\NamespaceB\ClassD');
        $classNamesA = array($interfaceA, $interfaceB, $interfaceC, $classB, $classC, $classD);
        // $expected = array(
        //     new ParsedResolutionContext($contextA, $classNamesA)
        // );
        $actual = $this->parser->parseSource($source);
        $expected = <<<'EOD'
namespace NamespaceA\NamespaceB;

use ClassF;
use ClassG as ClassH;
use NamespaceD\ClassI;
use NamespaceE\ClassJ as ClassK;
use NamespaceF\NamespaceG\ClassL;

\NamespaceA\NamespaceB\InterfaceA;
\NamespaceA\NamespaceB\InterfaceB;
\NamespaceA\NamespaceB\InterfaceC;
\NamespaceA\NamespaceB\ClassB;
\NamespaceA\NamespaceB\ClassC;
\NamespaceA\NamespaceB\ClassD;

namespace NamespaceC;

use ClassM;
use ClassN;

\NamespaceC\ClassE;
\NamespaceC\InterfaceD;

EOD;

        // var_dump($this->renderContexts($actual));
        $this->assertSame($expected, $this->renderContexts($actual));
        // $this->assertEquals($expected, $actual);
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
        $rendered = '';
        if (!$context->context()->primaryNamespace()->isRoot()) {
            $rendered .= sprintf("namespace %s;\n\n", $context->context()->primaryNamespace()->toRelative()->string());
        }

        foreach ($context->context()->useStatements() as $useStatement) {
            $rendered .= $useStatement->string() . ";\n";
        }

        if (count($context->context()->useStatements()) > 0) {
            $rendered .= "\n";
        }

        foreach ($context->classNames() as $className) {
            $rendered .= $className->string() . ";\n";
        }

        return $rendered;
    }
}
