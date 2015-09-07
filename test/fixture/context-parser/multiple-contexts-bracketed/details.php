<?php

return array(
    array(
        "namespace NamespaceA \ NamespaceB\n    {\n        use NamespaceD \ NamespaceE \ SymbolA as SymbolB ;\n        use SymbolC as SymbolD ;\n        use SymbolN as SymbolO, SymbolP ;",
        2, 5, 11,
        array(
            array("use NamespaceD \ NamespaceE \ SymbolA as SymbolB ;", 2, 9, 59),
            array("use SymbolC as SymbolD ;",                           1, 9, 9),
            array("use SymbolN as SymbolO, SymbolP ;",                  1, 9, 9),
        ),
        array(
            array("interface InterfaceA\n        {\n        }",                                                                                                     2, 9, 10),
            array("interface InterfaceB\n        {\n        }",                                                                                                     4, 9, 10),
            array("class ClassA\n        {\n            public function methodA()\n            {\n                \$a = function () {};\n            }\n        }", 4, 9, 10),
            array("class ClassB\n        {\n        }",                                                                                                             8, 9, 10),
            array("function FunctionA()\n        {\n        }",                                                                                                     4, 9, 10),
            array("function FunctionB()\n        {\n        }",                                                                                                     4, 9, 10),
        ),
    ),
    array(
        "namespace NamespaceC\n    {\n        use NamespaceF \ NamespaceG \ SymbolE as SymbolF ;\n        use SymbolG as SymbolH ;",
        8, 5, 77,
        array(
            array("use NamespaceF \ NamespaceG \ SymbolE as SymbolF ;", 2, 9, 112),
            array("use SymbolG as SymbolH ;",                           1, 9, 9),
        ),
        array(
            array("interface InterfaceC\n        {\n        }", 2, 9, 10),
            array("class ClassC\n        {\n        }",         4, 9, 10),
            array("function FunctionC()\n        {\n        }", 4, 9, 10),
        ),
    ),
    array(
        "namespace\n    {\n        use NamespaceH \ NamespaceI \ SymbolI as SymbolJ ;\n        use SymbolK as SymbolL ;",
        7, 5, 45,
        array(
            array("use NamespaceH \ NamespaceI \ SymbolI as SymbolJ ;", 2, 9, 69),
            array("use SymbolK as SymbolL ;",                           1, 9, 9),
        ),
        array(
            array("interface InterfaceD\n        {\n        }", 2, 9, 10),
            array("class ClassD\n        {\n        }",         4, 9, 10),
            array("function FunctionD()\n        {\n        }", 4, 9, 10),
        ),
    ),
);
