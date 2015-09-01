<?php

$i = 0;

$test->assertEquals(
    array(3, 5, 11, 35),
    array($actual[$i]->line, $actual[$i]->column, $actual[$i]->offset, $actual[$i]->size)
);

$i = 1;

$test->assertEquals(
    array(4, 5, 51, 22),
    array($actual[$i]->line, $actual[$i]->column, $actual[$i]->offset, $actual[$i]->size)
);
