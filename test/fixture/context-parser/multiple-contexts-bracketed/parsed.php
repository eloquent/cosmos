<?php

namespace NamespaceA\NamespaceB;

use NamespaceD\NamespaceE\SymbolA as SymbolB;
use SymbolC as SymbolD;
use namespace\SymbolC as SymbolM;
use SymbolN as SymbolO, SymbolP;

// interface \NamespaceA\NamespaceB\InterfaceA
// interface \NamespaceA\NamespaceB\InterfaceB
// class \NamespaceA\NamespaceB\ClassA
// class \NamespaceA\NamespaceB\ClassB
// function \NamespaceA\NamespaceB\FunctionA
// function \NamespaceA\NamespaceB\FunctionB

// end of context

namespace NamespaceC;

use NamespaceF\NamespaceG\SymbolE as SymbolF;
use SymbolG as SymbolH;

// interface \NamespaceC\InterfaceC
// class \NamespaceC\ClassC
// function \NamespaceC\FunctionC

// end of context

use NamespaceH\NamespaceI\SymbolI as SymbolJ;
use SymbolK as SymbolL;

// interface \InterfaceD
// class \ClassD
// function \FunctionD
