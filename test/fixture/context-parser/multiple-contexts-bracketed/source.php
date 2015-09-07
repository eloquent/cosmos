<?php

    namespace NamespaceA \ NamespaceB
    {
        use NamespaceD \ NamespaceE \ SymbolA as SymbolB ;
        use SymbolC as SymbolD ;
        use SymbolN as SymbolO, SymbolP ;

        interface InterfaceA
        {
        }

        interface InterfaceB
        {
        }

        class ClassA
        {
            public function methodA()
            {
                $a = function () {};
            }
        }

        class ClassB
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
        use NamespaceF \ NamespaceG \ SymbolE as SymbolF ;
        use SymbolG as SymbolH ;

        interface InterfaceC
        {
        }

        class ClassC
        {
        }

        function FunctionC()
        {
        }

        const CONSTANT_C = 'C';
    }

    namespace
    {
        use NamespaceH \ NamespaceI \ SymbolI as SymbolJ ;
        use SymbolK as SymbolL ;

        interface InterfaceD
        {
        }

        class ClassD
        {
        }

        function FunctionD()
        {
        }

        const CONSTANT_D = 'D';
    }
