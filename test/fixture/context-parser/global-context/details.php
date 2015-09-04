<?php

return array(
    array(
        array(3, 5, 11, 158),
        "use NamespaceD \ NamespaceE \ SymbolA as SymbolB ;\n    use SymbolC as SymbolD ;\n    use namespace \ SymbolC as SymbolM ;\n    use SymbolN as SymbolO, SymbolP ;",
        array(
            array(
                array(3, 5, 11, 50),
                "use NamespaceD \ NamespaceE \ SymbolA as SymbolB ;"
            ),
            array(
                array(4, 5, 66, 24),
                "use SymbolC as SymbolD ;"
            ),
            array(
                array(5, 5, 95, 36),
                "use namespace \ SymbolC as SymbolM ;"
            ),
            array(
                array(6, 5, 136, 33),
                "use SymbolN as SymbolO, SymbolP ;"
            ),
        ),
        array(
            array(
                array(8, 5, 175, 32),
                "interface InterfaceA\n    {\n    }"
            ),
            array(
                array(12, 5, 213, 32),
                "interface InterfaceB\n    {\n    }"
            ),
            array(
                array(16, 5, 251, 24),
                "class ClassA\n    {\n    }"
            ),
            array(
                array(20, 5, 281, 24),
                "class ClassB\n    {\n    }"
            ),
            array(
                array(24, 5, 311, 32),
                "function FunctionA()\n    {\n    }"
            ),
            array(
                array(28, 5, 349, 32),
                "function FunctionB()\n    {\n    }"
            ),
        ),
    ),
);
