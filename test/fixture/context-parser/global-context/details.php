<?php

return array(
    array(
        array(3, 5, 11, 117),
        "use NamespaceD \ NamespaceE \ SymbolA as SymbolB ;\n    use SymbolC as SymbolD ;\n    use SymbolN as SymbolO, SymbolP ;",
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
                array(5, 5, 95, 33),
                "use SymbolN as SymbolO, SymbolP ;"
            ),
        ),
        array(
            array(
                array(7, 5, 134, 32),
                "interface InterfaceA\n    {\n    }"
            ),
            array(
                array(11, 5, 172, 32),
                "interface InterfaceB\n    {\n    }"
            ),
            array(
                array(15, 5, 210, 24),
                "class ClassA\n    {\n    }"
            ),
            array(
                array(19, 5, 240, 24),
                "class ClassB\n    {\n    }"
            ),
            array(
                array(23, 5, 270, 32),
                "function FunctionA()\n    {\n    }"
            ),
            array(
                array(27, 5, 308, 32),
                "function FunctionB()\n    {\n    }"
            ),
        ),
    ),
);
