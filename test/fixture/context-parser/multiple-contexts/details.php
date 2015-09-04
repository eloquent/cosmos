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
        array(20, 5, 231, 156),
        "namespace NamespaceA \ NamespaceB ;\n\n    use NamespaceD \ NamespaceE\SymbolA as SymbolB ;\n    use SymbolC as SymbolD ;\n    use SymbolN as SymbolO, SymbolP ;",
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
                array(24, 5, 354, 33),
                "use SymbolN as SymbolO, SymbolP ;"
            ),
        ),
        array(
            array(
                array(26, 5, 393, 32),
                "interface InterfaceA\n    {\n    }"
            ),
            array(
                array(30, 5, 431, 32),
                "interface InterfaceB\n    {\n    }"
            ),
            array(
                array(34, 5, 469, 111),
                "class ClassA\n    {\n        public function methodA()\n        {\n            \$a = function () {};\n        }\n    }"
            ),
            array(
                array(42, 5, 586, 24),
                "class ClassB\n    {\n    }"
            ),
            array(
                array(46, 5, 616, 32),
                "function FunctionA()\n    {\n    }"
            ),
            array(
                array(50, 5, 654, 32),
                "function FunctionB()\n    {\n    }"
            ),
        ),
    ),
    array(
        array(57, 5, 749, 107),
        "namespace NamespaceC ;\n\n    use NamespaceF \ NamespaceG \ SymbolE as SymbolF ;\n    use SymbolG as SymbolH ;",
        array(
            array(
                array(59, 5, 777, 50),
                "use NamespaceF \ NamespaceG \ SymbolE as SymbolF ;"
            ),
            array(
                array(60, 5, 832, 24),
                "use SymbolG as SymbolH ;"
            ),
        ),
        array(
            array(
                array(62, 5, 862, 32),
                "interface InterfaceC\n    {\n    }"
            ),
            array(
                array(66, 5, 900, 24),
                "class ClassC\n    {\n    }"
            ),
            array(
                array(70, 5, 930, 32),
                "function FunctionC()\n    {\n    }"
            ),
        ),
    ),
);
