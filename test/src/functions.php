<?php

namespace
{
    use NamespaceA\ClassA;
    use NamespaceB\ClassB as ClassC;

    function functionA()
    {
    }
}

namespace NamespaceA\NamespaceB
{
    use ClassD;
    use ClassE as ClassF;

    function functionB()
    {
    }
}
