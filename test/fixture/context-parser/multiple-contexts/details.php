<?php

return array(
    array(
        "use NamespaceH \ NamespaceI \ SymbolI as SymbolJ ;\n    use SymbolK as SymbolL ;",
        2, 5, 11,
        array(
            array("use NamespaceH \ NamespaceI \ SymbolI as SymbolJ ;", 0, 5, 11),
            array("use SymbolK as SymbolL ;",                           1, 5, 5),
        ),
        array(
            array("interface InterfaceD\n    {\n    }", 2, 5, 6),
            array("class ClassD\n    {\n    }",         4, 5, 6),
            array("function FunctionD()\n    {\n    }", 4, 5, 6),
        ),
    ),
    array(
        "namespace NamespaceA \ NamespaceB ;\n\n    use NamespaceD \ NamespaceE \ SymbolA as SymbolB ;\n    use SymbolC as SymbolD ;\n    use SymbolN as SymbolO, SymbolP ;",
        6, 5, 35,
        array(
            array("use NamespaceD \ NamespaceE \ SymbolA as SymbolB ;", 2, 5, 76),
            array("use SymbolC as SymbolD ;",                           1, 5, 5),
            array("use SymbolN as SymbolO, SymbolP ;",                  1, 5, 5),
        ),
        array(
            array("interface InterfaceA\n    {\n    }",                                                                                     2, 5, 6),
            array("interface InterfaceB\n    {\n    }",                                                                                     4, 5, 6),
            array("class ClassA\n    {\n        public function methodA()\n        {\n            \$a = function () {};\n        }\n    }", 4, 5, 6),
            array("class ClassB\n    {\n    }",                                                                                             8, 5, 6),
            array("function FunctionA()\n    {\n    }",                                                                                     4, 5, 6),
            array("function FunctionB()\n    {\n    }",                                                                                     4, 5, 6),
        ),
    ),
    array(
        "namespace NamespaceC ;\n\n    use NamespaceF \ NamespaceG \ SymbolE as SymbolF ;\n    use SymbolG as SymbolH ;",
        7, 5, 63,
        array(
            array("use NamespaceF \ NamespaceG \ SymbolE as SymbolF ;", 2, 5, 91),
            array("use SymbolG as SymbolH ;",                           1, 5, 5),
        ),
        array(
            array("interface InterfaceC\n    {\n    }", 2, 5, 6),
            array("class ClassC\n    {\n    }",         4, 5, 6),
            array("function FunctionC()\n    {\n    }", 4, 5, 6),
        ),
    ),
);
