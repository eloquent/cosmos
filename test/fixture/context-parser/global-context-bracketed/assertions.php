<?php

$i = 0;

$test->assertEquals(
    array(3, 5, 11, 194),
    array($actual[$i]->line, $actual[$i]->column, $actual[$i]->offset, $actual[$i]->size)
);