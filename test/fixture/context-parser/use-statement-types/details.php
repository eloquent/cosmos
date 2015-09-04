<?php

return array(
    array(
        array(3, 5, 11, 216),
        "namespace NamespaceA \ NamespaceB;\n\n    use const NamespaceD \ NamespaceE \ SymbolA as SymbolB ;\n    use const SymbolC as SymbolD ;\n    use function SymbolC as SymbolM ;\n    use function SymbolN as SymbolO, SymbolP ;",
        array(
            array(
                array(5, 5, 51, 56),
                "use const NamespaceD \ NamespaceE \ SymbolA as SymbolB ;"
            ),
            array(
                array(6, 5, 112, 30),
                "use const SymbolC as SymbolD ;"
            ),
            array(
                array(7, 5, 147, 33),
                "use function SymbolC as SymbolM ;"
            ),
            array(
                array(8, 5, 185, 42),
                "use function SymbolN as SymbolO, SymbolP ;"
            ),
        ),
        array(),
    ),
);
