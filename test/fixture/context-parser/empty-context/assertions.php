<?php

$i = 0;

$test->assertEquals(
    array(2, 1, 6, 0),
    array($actual[$i]->line, $actual[$i]->column, $actual[$i]->offset, $actual[$i]->size)
);
$test->assertSame("\n", $tokens[$actual[$i]->startIndex][1]);
$test->assertSame("\n", $tokens[$actual[$i]->endIndex][1]);
