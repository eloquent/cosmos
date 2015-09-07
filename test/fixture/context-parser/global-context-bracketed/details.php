<?php

return array(
    array(
        "namespace\n    {\n        use NamespaceD \ NamespaceE \ SymbolA as SymbolB ;\n        use SymbolC as SymbolD ;\n        use SymbolN as SymbolO, SymbolP ;",
        2, 5, 11,
        array(
            array("use NamespaceD \ NamespaceE \ SymbolA as SymbolB ;", 2, 9, 35),
            array("use SymbolC as SymbolD ;",                           1, 9, 9),
            array("use SymbolN as SymbolO, SymbolP ;",                  1, 9, 9),
        ),
        array(
            array("interface InterfaceA\n        {\n        }", 2, 9, 10),
            array("interface InterfaceB\n        {\n        }", 4, 9, 10),
            array("class ClassA\n        {\n        }",         4, 9, 10),
            array("class ClassB\n        {\n        }",         4, 9, 10),
            array("function FunctionA()\n        {\n        }", 4, 9, 10),
            array("function FunctionB()\n        {\n        }", 4, 9, 10),
        ),
    ),
);
