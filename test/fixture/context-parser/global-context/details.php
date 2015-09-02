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
    ),
);
