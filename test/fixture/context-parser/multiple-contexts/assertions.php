<?php

$i = 0;

$test->assertEquals(
    array(3, 5, 11, 79),
    array($actual[$i]->line, $actual[$i]->column, $actual[$i]->offset, $actual[$i]->size)
);
$test->assertSame('use', $tokens[$actual[$i]->startIndex][1]);
$test->assertSame(';', $tokens[$actual[$i]->endIndex][1]);

$i = 1;

$test->assertEquals(
    array(20, 5, 191, 197),
    array($actual[$i]->line, $actual[$i]->column, $actual[$i]->offset, $actual[$i]->size)
);
$test->assertSame('namespace', $tokens[$actual[$i]->startIndex][1]);
$test->assertSame(';', $tokens[$actual[$i]->endIndex][1]);

$i = 2;

$test->assertEquals(
    array(58, 5, 654, 107),
    array($actual[$i]->line, $actual[$i]->column, $actual[$i]->offset, $actual[$i]->size)
);
$test->assertSame('namespace', $tokens[$actual[$i]->startIndex][1]);
$test->assertSame(';', $tokens[$actual[$i]->endIndex][1]);
