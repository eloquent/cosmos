<?php

return array(
    array(
        array(3, 5, 11, 173),
        "namespace NamespaceA \ NamespaceB\n    {\n        use NamespaceD \ NamespaceE \ SymbolA as SymbolB ;\n        use SymbolC as SymbolD ;\n        use SymbolN as SymbolO, SymbolP ;",
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
                array(7, 9, 151, 33),
                "use SymbolN as SymbolO, SymbolP ;"
            ),
        ),
        array(
            array(
                array(9, 9, 194, 40),
                "interface InterfaceA\n        {\n        }"
            ),
            array(
                array(13, 9, 244, 40),
                "interface InterfaceB\n        {\n        }"
            ),
            array(
                array(17, 9, 294, 32),
                "class ClassA\n        {\n        }"
            ),
            array(
                array(21, 9, 336, 32),
                "class ClassB\n        {\n        }"
            ),
            array(
                array(25, 9, 378, 40),
                "function FunctionA()\n        {\n        }"
            ),
            array(
                array(29, 9, 428, 40),
                "function FunctionB()\n        {\n        }"
            ),
        ),
    ),
    array(
        array(37, 5, 545, 118),
        "namespace NamespaceC\n    {\n        use NamespaceF \ NamespaceG \ SymbolE as SymbolF ;\n        use SymbolG as SymbolH ;",
        array(
            array(
                array(39, 9, 580, 50),
                "use NamespaceF \ NamespaceG \ SymbolE as SymbolF ;"
            ),
            array(
                array(40, 9, 639, 24),
                "use SymbolG as SymbolH ;"
            ),
        ),
        array(
            array(
                array(42, 9, 673, 40),
                "interface InterfaceC\n        {\n        }"
            ),
            array(
                array(46, 9, 723, 32),
                "class ClassC\n        {\n        }"
            ),
            array(
                array(50, 9, 765, 40),
                "function FunctionC()\n        {\n        }"
            ),
        ),
    ),
    array(
        array(57, 5, 850, 107),
        "namespace\n    {\n        use NamespaceH \ NamespaceI \ SymbolI as SymbolJ ;\n        use SymbolK as SymbolL ;",
        array(
            array(
                array(59, 9, 874, 50),
                "use NamespaceH \ NamespaceI \ SymbolI as SymbolJ ;"
            ),
            array(
                array(60, 9, 933, 24),
                "use SymbolK as SymbolL ;"
            ),
        ),
        array(
            array(
                array(62, 9, 967, 40),
                "interface InterfaceD\n        {\n        }"
            ),
            array(
                array(66, 9, 1017, 32),
                "class ClassD\n        {\n        }"
            ),
            array(
                array(70, 9, 1059, 40),
                "function FunctionD()\n        {\n        }"
            ),
        ),
    ),
);
