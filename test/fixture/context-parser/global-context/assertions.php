<?php

$i = 0;

$test->assertEquals(
    array(3, 5, 11, 158),
    array($actual[$i]->line, $actual[$i]->column, $actual[$i]->offset, $actual[$i]->size)
);
$test->assertSame('use', $tokens[$actual[$i]->startIndex][1]);
$test->assertSame(';', $tokens[$actual[$i]->endIndex][1]);
