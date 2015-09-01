<?php

$i = 0;

$test->assertEquals(
    array(3, 5, 11, 218),
    array($actual[$i]->line, $actual[$i]->column, $actual[$i]->offset, $actual[$i]->size)
);
$test->assertSame('namespace', $tokens[$actual[$i]->startIndex][1]);
$test->assertSame(';', $tokens[$actual[$i]->endIndex][1]);

$i = 1;

$test->assertEquals(
    array(38, 5, 506, 118),
    array($actual[$i]->line, $actual[$i]->column, $actual[$i]->offset, $actual[$i]->size)
);
$test->assertSame('namespace', $tokens[$actual[$i]->startIndex][1]);
$test->assertSame(';', $tokens[$actual[$i]->endIndex][1]);

$i = 2;

$test->assertEquals(
    array(58, 5, 767, 107),
    array($actual[$i]->line, $actual[$i]->column, $actual[$i]->offset, $actual[$i]->size)
);
$test->assertSame('namespace', $tokens[$actual[$i]->startIndex][1]);
$test->assertSame(';', $tokens[$actual[$i]->endIndex][1]);
