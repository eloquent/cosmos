<?php

return array(
    array(
        "namespace NamespaceA \ NamespaceB;\n\n    use const NamespaceD \ NamespaceE \ SymbolA as SymbolB ;\n    use const SymbolC as SymbolD ;\n    use function SymbolC as SymbolM ;\n    use function SymbolN as SymbolO, SymbolP ;",
        2, 5, 11,
        array(
            array("use const NamespaceD \ NamespaceE \ SymbolA as SymbolB ;", 2, 5, 51),
            array("use const SymbolC as SymbolD ;",                           1, 5, 5),
            array("use function SymbolC as SymbolM ;",                        1, 5, 5),
            array("use function SymbolN as SymbolO, SymbolP ;",               1, 5, 5),

        ),
        array(),
    ),
);
