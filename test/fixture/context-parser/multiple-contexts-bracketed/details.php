<?php

return array(
    array(
        array(3, 5, 11, 218),
        "namespace NamespaceA \ NamespaceB\n    {\n        use NamespaceD \ NamespaceE \ SymbolA as SymbolB ;\n        use SymbolC as SymbolD ;\n        use namespace \ SymbolC as SymbolM ;\n        use SymbolN as SymbolO, SymbolP ;",
        array(
            array(
                array(5, 9, 59, 50),
                "use NamespaceD \ NamespaceE \ SymbolA as SymbolB ;"
            ),
            array(
                array(6, 9, 118, 24),
                "use SymbolC as SymbolD ;"
            ),
            array(
                array(7, 9, 151, 36),
                "use namespace \ SymbolC as SymbolM ;"
            ),
            array(
                array(8, 9, 196, 33),
                "use SymbolN as SymbolO, SymbolP ;"
            ),
        ),
        array(
            array(
                array(10, 9, 239, 40),
                "interface InterfaceA\n        {\n        }"
            ),
            array(
                array(14, 9, 289, 40),
                "interface InterfaceB\n        {\n        }"
            ),
            array(
                array(18, 9, 339, 32),
                "class ClassA\n        {\n        }"
            ),
            array(
                array(22, 9, 381, 32),
                "class ClassB\n        {\n        }"
            ),
            array(
                array(26, 9, 423, 40),
                "function FunctionA()\n        {\n        }"
            ),
            array(
                array(30, 9, 473, 40),
                "function FunctionB()\n        {\n        }"
            ),
        ),
    ),
    array(
        array(38, 5, 590, 118),
        "namespace NamespaceC\n    {\n        use NamespaceF \ NamespaceG \ SymbolE as SymbolF ;\n        use SymbolG as SymbolH ;",
        array(
            array(
                array(40, 9, 625, 50),
                "use NamespaceF \ NamespaceG \ SymbolE as SymbolF ;"
            ),
            array(
                array(41, 9, 684, 24),
                "use SymbolG as SymbolH ;"
            ),
        ),
        array(
            array(
                array(43, 9, 718, 40),
                "interface InterfaceC\n        {\n        }"
            ),
            array(
                array(47, 9, 768, 32),
                "class ClassC\n        {\n        }"
            ),
            array(
                array(51, 9, 810, 40),
                "function FunctionC()\n        {\n        }"
            ),
        ),
    ),
    array(
        array(58, 5, 895, 107),
        "namespace\n    {\n        use NamespaceH \ NamespaceI \ SymbolI as SymbolJ ;\n        use SymbolK as SymbolL ;",
        array(
            array(
                array(60, 9, 919, 50),
                "use NamespaceH \ NamespaceI \ SymbolI as SymbolJ ;"
            ),
            array(
                array(61, 9, 978, 24),
                "use SymbolK as SymbolL ;"
            ),
        ),
        array(
            array(
                array(63, 9, 1012, 40),
                "interface InterfaceD\n        {\n        }"
            ),
            array(
                array(67, 9, 1062, 32),
                "class ClassD\n        {\n        }"
            ),
            array(
                array(71, 9, 1104, 40),
                "function FunctionD()\n        {\n        }"
            ),
        ),
    ),
);
