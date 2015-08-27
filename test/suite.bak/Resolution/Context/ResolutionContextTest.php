<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Context;

use Eloquent\Cosmos\Resolution\Context\Parser\ParserPosition;
use Eloquent\Cosmos\Resolution\Context\Renderer\ResolutionContextRenderer;
use Eloquent\Cosmos\Symbol\Factory\SymbolFactory;
use Eloquent\Cosmos\Symbol\QualifiedSymbol;
use Eloquent\Cosmos\Symbol\Symbol;
use Eloquent\Cosmos\Symbol\SymbolType;
use Eloquent\Cosmos\UseStatement\UseStatement;
use Eloquent\Cosmos\UseStatement\UseStatementType;
use NamespaceA\NamespaceB\ClassA;
use Phake;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionFunction;

class ResolutionContextTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->symbolFactory = new SymbolFactory();
        $this->primaryNamespace = Symbol::fromString('\VendorA\PackageA');
        $this->useStatements = array(
            UseStatement::create(Symbol::fromString('\VendorB\PackageB')),
            UseStatement::create(Symbol::fromString('\VendorC\PackageC')),
            UseStatement::create(Symbol::fromString('\VendorD\PackageD'), null, UseStatementType::FUNCT1ON()),
            UseStatement::create(Symbol::fromString('\VendorE\PackageE'), null, UseStatementType::FUNCT1ON()),
            UseStatement::create(Symbol::fromString('\VendorF\PackageF'), null, UseStatementType::CONSTANT()),
            UseStatement::create(Symbol::fromString('\VendorG\PackageG'), null, UseStatementType::CONSTANT()),
        );
        $this->context = new ResolutionContext($this->primaryNamespace, $this->useStatements, $this->symbolFactory);

        $this->contextRenderer = ResolutionContextRenderer::instance();

        $this->fixturePath = dirname(dirname(dirname(__DIR__))) . '/fixture/contexts.php';
        $this->fixtureStream = fopen($this->fixturePath, 'rb');

        require_once $this->fixturePath;
    }

    protected function tearDown()
    {
        parent::tearDown();

        fclose($this->fixtureStream);
    }

    public function testConstructor()
    {
        $this->assertSame($this->primaryNamespace, $this->context->primaryNamespace());
        $this->assertSame($this->useStatements, $this->context->useStatements());
    }

    public function testConstructorDefaults()
    {
        $this->context = new ResolutionContext();

        $this->assertEquals(QualifiedSymbol::globalNamespace(), $this->context->primaryNamespace());
        $this->assertSame(array(), $this->context->useStatements());
    }

    public function testFromObject()
    {
        $actual = ResolutionContext::fromObject(new ClassA());
        $expected = <<<'EOD'
namespace NamespaceA\NamespaceB;

use NamespaceD\NamespaceE\SymbolA as SymbolB;
use SymbolC as SymbolD;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testFromSymbol()
    {
        $actual = ResolutionContext::fromSymbol(Symbol::fromString('\NamespaceA\NamespaceB\ClassA'));
        $expected = <<<'EOD'
namespace NamespaceA\NamespaceB;

use NamespaceD\NamespaceE\SymbolA as SymbolB;
use SymbolC as SymbolD;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testFromFunctionSymbol()
    {
        $actual = ResolutionContext::fromFunctionSymbol(Symbol::fromString('\FunctionD'));
        $expected = <<<'EOD'
use NamespaceH\NamespaceI\SymbolI as SymbolJ;
use SymbolK as SymbolL;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testFromClass()
    {
        $actual = ResolutionContext::fromClass(new ReflectionClass('NamespaceA\NamespaceB\ClassA'));
        $expected = <<<'EOD'
namespace NamespaceA\NamespaceB;

use NamespaceD\NamespaceE\SymbolA as SymbolB;
use SymbolC as SymbolD;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testFromFunction()
    {
        $actual = ResolutionContext::fromFunction(new ReflectionFunction('FunctionD'));
        $expected = <<<'EOD'
use NamespaceH\NamespaceI\SymbolI as SymbolJ;
use SymbolK as SymbolL;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testFromFile()
    {
        $actual = ResolutionContext::fromFile($this->fixturePath);
        $expected = <<<'EOD'
namespace NamespaceA\NamespaceB;

use NamespaceD\NamespaceE\SymbolA as SymbolB;
use SymbolC as SymbolD;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testFromFileByIndex()
    {
        $actual = ResolutionContext::fromFileByIndex($this->fixturePath, 2);
        $expected = <<<'EOD'
use NamespaceH\NamespaceI\SymbolI as SymbolJ;
use SymbolK as SymbolL;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testFromFileByPosition()
    {
        $position = new ParserPosition(24, 111);
        $actual = ResolutionContext::fromFileByPosition($this->fixturePath, $position);
        $expected = <<<'EOD'
namespace NamespaceA\NamespaceB;

use NamespaceD\NamespaceE\SymbolA as SymbolB;
use SymbolC as SymbolD;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testFromStream()
    {
        $actual = ResolutionContext::fromStream($this->fixtureStream);
        $expected = <<<'EOD'
namespace NamespaceA\NamespaceB;

use NamespaceD\NamespaceE\SymbolA as SymbolB;
use SymbolC as SymbolD;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testFromStreamByIndex()
    {
        $actual = ResolutionContext::fromStreamByIndex($this->fixtureStream, 2);
        $expected = <<<'EOD'
use NamespaceH\NamespaceI\SymbolI as SymbolJ;
use SymbolK as SymbolL;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testFromStreamByPosition()
    {
        $position = new ParserPosition(24, 111);
        $actual = ResolutionContext::fromStreamByPosition($this->fixtureStream, $position);
        $expected = <<<'EOD'
namespace NamespaceA\NamespaceB;

use NamespaceD\NamespaceE\SymbolA as SymbolB;
use SymbolC as SymbolD;

EOD;

        $this->assertSame($expected, $this->contextRenderer->renderContext($actual));
    }

    public function testUseStatementsByType()
    {
        $typeUseStatements = array($this->useStatements[0], $this->useStatements[1]);
        $functionUseStatements = array($this->useStatements[2], $this->useStatements[3]);
        $constantUseStatements = array($this->useStatements[4], $this->useStatements[5]);

        $this->assertSame($typeUseStatements, $this->context->useStatementsByType(UseStatementType::TYPE()));
        $this->assertSame($functionUseStatements, $this->context->useStatementsByType(UseStatementType::FUNCT1ON()));
        $this->assertSame($constantUseStatements, $this->context->useStatementsByType(UseStatementType::CONSTANT()));
    }

    public function testSymbolByFirstAtom()
    {
        $this->context = new ResolutionContext(
            Symbol::fromString('\foo'),
            array(
                UseStatement::create(
                    Symbol::fromString('\NamespaceA\NamespaceB\SymbolA'),
                    Symbol::fromString('SymbolB')
                ),
                UseStatement::create(Symbol::fromString('\NamespaceC\NamespaceD')),
                UseStatement::create(Symbol::fromString('\SymbolC')),

                UseStatement::create(Symbol::fromString(
                    '\NamespaceE\NamespaceF\SymbolD'),
                    Symbol::fromString('SymbolE'),
                    UseStatementType::FUNCT1ON()
                ),
                UseStatement::create(Symbol::fromString('\NamespaceG\SymbolF'), null, UseStatementType::FUNCT1ON()),
                UseStatement::create(Symbol::fromString('\SymbolC'), null, UseStatementType::FUNCT1ON()),

                UseStatement::create(Symbol::fromString(
                    '\NamespaceH\NamespaceI\SymbolG'),
                    Symbol::fromString('SymbolH'),
                    UseStatementType::CONSTANT()
                ),
                UseStatement::create(Symbol::fromString('\NamespaceJ\SymbolI'), null, UseStatementType::CONSTANT()),
                UseStatement::create(Symbol::fromString('\SymbolC'), null, UseStatementType::CONSTANT()),
            )
        );

        $this->assertSame(
            '\NamespaceA\NamespaceB\SymbolA',
            $this->context->symbolByFirstAtom(Symbol::fromString('SymbolB'))->string()
        );
        $this->assertSame(
            '\NamespaceC\NamespaceD',
            $this->context->symbolByFirstAtom(Symbol::fromString('NamespaceD'))->string()
        );
        $this->assertSame('\SymbolC', $this->context->symbolByFirstAtom(Symbol::fromString('SymbolC'))->string());
        $this->assertNull($this->context->symbolByFirstAtom(Symbol::fromString('SymbolA')));
        $this->assertNull($this->context->symbolByFirstAtom(Symbol::fromString('SymbolF')));

        $this->assertSame(
            '\NamespaceE\NamespaceF\SymbolD',
            $this->context->symbolByFirstAtom(Symbol::fromString('SymbolE'), SymbolType::FUNCT1ON())->string()
        );
        $this->assertSame(
            '\NamespaceG\SymbolF',
            $this->context->symbolByFirstAtom(Symbol::fromString('SymbolF'), SymbolType::FUNCT1ON())->string()
        );
        $this->assertSame(
            '\SymbolC',
            $this->context->symbolByFirstAtom(Symbol::fromString('SymbolC'), SymbolType::FUNCT1ON())->string()
        );
        $this->assertNull($this->context->symbolByFirstAtom(Symbol::fromString('SymbolA'), SymbolType::FUNCT1ON()));
        $this->assertNull($this->context->symbolByFirstAtom(Symbol::fromString('SymbolB'), SymbolType::FUNCT1ON()));

        $this->assertSame(
            '\NamespaceH\NamespaceI\SymbolG',
            $this->context->symbolByFirstAtom(Symbol::fromString('SymbolH'), SymbolType::CONSTANT())->string()
        );
        $this->assertSame(
            '\NamespaceJ\SymbolI',
            $this->context->symbolByFirstAtom(Symbol::fromString('SymbolI'), SymbolType::CONSTANT())->string()
        );
        $this->assertSame(
            '\SymbolC',
            $this->context->symbolByFirstAtom(Symbol::fromString('SymbolC'), SymbolType::CONSTANT())->string()
        );
        $this->assertNull($this->context->symbolByFirstAtom(Symbol::fromString('SymbolA'), SymbolType::CONSTANT()));
        $this->assertNull($this->context->symbolByFirstAtom(Symbol::fromString('SymbolB'), SymbolType::CONSTANT()));
    }

    public function testResolve()
    {
        $this->assertSame(
            '\VendorB\PackageB\PackageC\SymbolD',
            $this->context->resolve(Symbol::fromString('PackageB\PackageC\SymbolD'))->string()
        );
    }

    public function testAccept()
    {
        $visitor = Phake::mock('Eloquent\Cosmos\Resolution\Context\ResolutionContextVisitorInterface');
        $this->context->accept($visitor);

        Phake::verify($visitor)->visitResolutionContext($this->identicalTo($this->context));
    }
}
