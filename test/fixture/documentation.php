<?php

namespace NamespaceA;

use Eloquent\Cosmos\Resolution\FixedContextSymbolResolver;
use Eloquent\Cosmos\Symbol\Symbol;
use NamespaceB\SymbolA as SymbolB;
use SymbolC;

class ClassA
{
    public function methodA()
    {
        $resolver = FixedContextSymbolResolver::fromObject($this);

        echo $resolver->resolve(Symbol::fromString('SymbolB')); // outputs '\NamespaceB\SymbolA'
    }
}

namespace NamespaceE;

function SymbolD()
{
}
