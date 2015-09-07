<?php

return array(
    array(
        "use NamespaceD \ NamespaceE \ SymbolA as SymbolB ;\n    use SymbolC as SymbolD ;\n    use SymbolN as SymbolO, SymbolP ;",
        2, 5, 11,
        array(
            array("use NamespaceD \ NamespaceE \ SymbolA as SymbolB ;", 0, 5, 11),
            array("use SymbolC as SymbolD ;",                           1, 5, 5),
            array("use SymbolN as SymbolO, SymbolP ;",                  1, 5, 5),
        ),
        array(
            array("interface InterfaceA\n    {\n    }", 2, 5, 6),
            array("interface InterfaceB\n    {\n    }", 4, 5, 6),
            array("class ClassA\n    {\n    }",         4, 5, 6),
            array("class ClassB\n    {\n    }",         4, 5, 6),
            array("function FunctionA()\n    {\n    }", 4, 5, 6),
            array("function FunctionB()\n    {\n    }", 4, 5, 6),
        ),
    ),
);
