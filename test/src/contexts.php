<?php

namespace NamespaceA\NamespaceB
{
    use NamespaceD\NamespaceE\SymbolA as SymbolB;
    use SymbolC as SymbolD;

    interface InterfaceA
    {
    }

    interface InterfaceB
    {
    }

    class ClassA
    {
    }

    class ClassB
    {
    }

    trait TraitA
    {
    }

    trait TraitB
    {
    }

    function FunctionA()
    {
    }

    function FunctionB()
    {
    }

    const CONSTANT_A = 'A';
    const CONSTANT_B = 'B';
}

namespace NamespaceC
{
    use NamespaceF\NamespaceG\SymbolE as SymbolF;
    use SymbolG as SymbolH;

    interface InterfaceC
    {
    }

    class ClassC
    {
    }

    trait TraitC
    {
    }

    function FunctionC()
    {
    }

    const CONSTANT_C = 'C';
}

namespace
{
    use NamespaceH\NamespaceI\SymbolI as SymbolJ;
    use SymbolK as SymbolL;

    interface InterfaceD
    {
    }

    class ClassD
    {
    }

    trait TraitD
    {
    }

    function FunctionD()
    {
    }

    const CONSTANT_D = 'D';
}
