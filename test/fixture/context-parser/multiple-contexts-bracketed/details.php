<?php

return array(
    array(
        array(3, 5, 11, 218),
        "namespace NamespaceA \ NamespaceB\n    {\n        use NamespaceD \ NamespaceE \ SymbolA as SymbolB ;\n        use SymbolC as SymbolD ;\n        use namespace \ SymbolC as SymbolM ;\n        use SymbolN as SymbolO, SymbolP ;",
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
                array(7, 9, 151, 36),
                "use namespace \ SymbolC as SymbolM ;"
            ),
            array(
                array(8, 9, 196, 33),
                "use SymbolN as SymbolO, SymbolP ;"
            ),
        ),
    ),
    array(
        array(38, 5, 506, 118),
        "namespace NamespaceC\n    {\n        use NamespaceF \ NamespaceG \ SymbolE as SymbolF ;\n        use SymbolG as SymbolH ;",
        array(
            array(
                array(40, 9, 541, 50),
                "use NamespaceF \ NamespaceG \ SymbolE as SymbolF ;"
            ),
            array(
                array(41, 9, 600, 24),
                "use SymbolG as SymbolH ;"
            ),
        ),
    ),
    array(
        array(58, 5, 767, 107),
        "namespace\n    {\n        use NamespaceH \ NamespaceI \ SymbolI as SymbolJ ;\n        use SymbolK as SymbolL ;",
        array(
            array(
                array(60, 9, 791, 50),
                "use NamespaceH \ NamespaceI \ SymbolI as SymbolJ ;"
            ),
            array(
                array(61, 9, 850, 24),
                "use SymbolK as SymbolL ;"
            ),
        ),
    ),
);
