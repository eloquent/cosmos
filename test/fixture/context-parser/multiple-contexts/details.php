<?php

return array(
    array(
        array(3, 5, 11, 79),
        "use NamespaceH \ NamespaceI \ SymbolI as SymbolJ ;\n    use SymbolK as SymbolL ;",
        array(
            array(
                array(3, 5, 11, 50),
                "use NamespaceH \ NamespaceI \ SymbolI as SymbolJ ;"
            ),
            array(
                array(4, 5, 66, 24),
                "use SymbolK as SymbolL ;"
            ),
        ),
    ),
    array(
        array(20, 5, 191, 197),
        "namespace NamespaceA \ NamespaceB ;\n\n    use NamespaceD \ NamespaceE\SymbolA as SymbolB ;\n    use SymbolC as SymbolD ;\n    use namespace \ SymbolC as SymbolM ;\n    use SymbolN as SymbolO, SymbolP ;",
        array(
            array(
                array(22, 5, 232, 48),
                "use NamespaceD \ NamespaceE\SymbolA as SymbolB ;"
            ),
            array(
                array(23, 5, 285, 24),
                "use SymbolC as SymbolD ;"
            ),
            array(
                array(24, 5, 314, 36),
                "use namespace \ SymbolC as SymbolM ;"
            ),
            array(
                array(25, 5, 355, 33),
                "use SymbolN as SymbolO, SymbolP ;"
            ),
        ),
    ),
    array(
        array(58, 5, 654, 107),
        "namespace NamespaceC ;\n\n    use NamespaceF \ NamespaceG \ SymbolE as SymbolF ;\n    use SymbolG as SymbolH ;",
        array(
            array(
                array(60, 5, 682, 50),
                "use NamespaceF \ NamespaceG \ SymbolE as SymbolF ;"
            ),
            array(
                array(61, 5, 737, 24),
                "use SymbolG as SymbolH ;"
            ),
        ),
    ),
);
