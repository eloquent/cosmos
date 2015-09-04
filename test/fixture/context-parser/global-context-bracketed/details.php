<?php

return array(
    array(
        array(3, 5, 11, 149),
        "namespace\n    {\n        use NamespaceD \ NamespaceE \ SymbolA as SymbolB ;\n        use SymbolC as SymbolD ;\n        use SymbolN as SymbolO, SymbolP ;",
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
                array(7, 9, 127, 33),
                "use SymbolN as SymbolO, SymbolP ;"
            ),
        ),
        array(
            array(
                array(9, 9, 170, 40),
                "interface InterfaceA\n        {\n        }"
            ),
            array(
                array(13, 9, 220, 40),
                "interface InterfaceB\n        {\n        }"
            ),
            array(
                array(17, 9, 270, 32),
                "class ClassA\n        {\n        }"
            ),
            array(
                array(21, 9, 312, 32),
                "class ClassB\n        {\n        }"
            ),
            array(
                array(25, 9, 354, 40),
                "function FunctionA()\n        {\n        }"
            ),
            array(
                array(29, 9, 404, 40),
                "function FunctionB()\n        {\n        }"
            ),
        ),
    ),
);
