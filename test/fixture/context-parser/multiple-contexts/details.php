<?php

return array(
    array(
        array(3, 5, 11, 79),
        "use NamespaceH \ NamespaceI \ SymbolI as SymbolJ ;\n    use SymbolK as SymbolL ;",
        array(
            array(
                array(3, 5, 11, 50),
                "use NamespaceH \ NamespaceI \ SymbolI as SymbolJ ;"
            ),
            array(
                array(4, 5, 66, 24),
                "use SymbolK as SymbolL ;"
            ),
        ),
        array(
            array(
                array(6, 5, 96, 32),
                "interface InterfaceD\n    {\n    }"
            ),
            array(
                array(10, 5, 134, 24),
                "class ClassD\n    {\n    }"
            ),
            array(
                array(14, 5, 164, 32),
                "function FunctionD()\n    {\n    }"
            ),
        ),
    ),
    array(
        array(20, 5, 231, 197),
        "namespace NamespaceA \ NamespaceB ;\n\n    use NamespaceD \ NamespaceE\SymbolA as SymbolB ;\n    use SymbolC as SymbolD ;\n    use namespace \ SymbolC as SymbolM ;\n    use SymbolN as SymbolO, SymbolP ;",
        array(
            array(
                array(22, 5, 272, 48),
                "use NamespaceD \ NamespaceE\SymbolA as SymbolB ;"
            ),
            array(
                array(23, 5, 325, 24),
                "use SymbolC as SymbolD ;"
            ),
            array(
                array(24, 5, 354, 36),
                "use namespace \ SymbolC as SymbolM ;"
            ),
            array(
                array(25, 5, 395, 33),
                "use SymbolN as SymbolO, SymbolP ;"
            ),
        ),
        array(
            array(
                array(27, 5, 434, 32),
                "interface InterfaceA\n    {\n    }"
            ),
            array(
                array(31, 5, 472, 32),
                "interface InterfaceB\n    {\n    }"
            ),
            array(
                array(35, 5, 510, 111),
                "class ClassA\n    {\n        public function methodA()\n        {\n            \$a = function () {};\n        }\n    }"
            ),
            array(
                array(43, 5, 627, 24),
                "class ClassB\n    {\n    }"
            ),
            array(
                array(47, 5, 657, 32),
                "function FunctionA()\n    {\n    }"
            ),
            array(
                array(51, 5, 695, 32),
                "function FunctionB()\n    {\n    }"
            ),
        ),
    ),
    array(
        array(58, 5, 790, 107),
        "namespace NamespaceC ;\n\n    use NamespaceF \ NamespaceG \ SymbolE as SymbolF ;\n    use SymbolG as SymbolH ;",
        array(
            array(
                array(60, 5, 818, 50),
                "use NamespaceF \ NamespaceG \ SymbolE as SymbolF ;"
            ),
            array(
                array(61, 5, 873, 24),
                "use SymbolG as SymbolH ;"
            ),
        ),
        array(
            array(
                array(63, 5, 903, 32),
                "interface InterfaceC\n    {\n    }"
            ),
            array(
                array(67, 5, 941, 24),
                "class ClassC\n    {\n    }"
            ),
            array(
                array(71, 5, 971, 32),
                "function FunctionC()\n    {\n    }"
            ),
        ),
    ),
);
