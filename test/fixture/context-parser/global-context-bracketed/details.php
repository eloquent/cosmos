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
    ),
);
