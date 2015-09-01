<?php

$i = 0;

$test->assertEquals(
    array(3, 5, 11, 248),
    array($actual[$i]->line, $actual[$i]->column, $actual[$i]->offset, $actual[$i]->size)
);
$test->assertSame('namespace', $tokens[$actual[$i]->startIndex][1]);
$test->assertSame(';', $tokens[$actual[$i]->endIndex][1]);
