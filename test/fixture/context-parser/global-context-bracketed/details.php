<?php

return array(
    array(
        array(3, 5, 11, 194),
        "namespace\n    {\n        use NamespaceD \ NamespaceE \ SymbolA as SymbolB ;\n        use SymbolC as SymbolD ;\n        use namespace \ SymbolC as SymbolM ;\n        use SymbolN as SymbolO, SymbolP ;",
        array(
            array(
                array(5, 9, 35, 50),
                "use NamespaceD \ NamespaceE \ SymbolA as SymbolB ;"
            ),
            array(
                array(6, 9, 94, 24),
                "use SymbolC as SymbolD ;"
            ),
            array(
                array(7, 9, 127, 36),
                "use namespace \ SymbolC as SymbolM ;"
            ),
            array(
                array(8, 9, 172, 33),
                "use SymbolN as SymbolO, SymbolP ;"
            ),
        ),
        array(
            array(
                array(10, 9, 215, 40),
                "interface InterfaceA\n        {\n        }"
            ),
            array(
                array(14, 9, 265, 40),
                "interface InterfaceB\n        {\n        }"
            ),
            array(
                array(18, 9, 315, 32),
                "class ClassA\n        {\n        }"
            ),
            array(
                array(22, 9, 357, 32),
                "class ClassB\n        {\n        }"
            ),
            array(
                array(26, 9, 399, 40),
                "function FunctionA()\n        {\n        }"
            ),
            array(
                array(30, 9, 449, 40),
                "function FunctionB()\n        {\n        }"
            ),
        ),
    ),
);
