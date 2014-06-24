<?php

/*
 * This file is part of the Cosmos package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Cosmos\Resolution\Context\Persistence;

use Eloquent\Liberator\Liberator;
use Eloquent\Pathogen\FileSystem\FileSystemPath;
use Icecave\Isolator\Isolator;
use PHPUnit_Framework_TestCase;
use Phake;

class StreamEditorTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->bufferSize = 2;
        $this->isolator = Phake::partialMock(Isolator::className());
        $this->editor = new StreamEditor($this->bufferSize, $this->isolator);

        $this->stream = fopen('php://memory', 'rb+');
        $this->path = FileSystemPath::fromString('/path/to/file');
    }

    protected function tearDown()
    {
        parent::tearDown();

        fclose($this->stream);
    }

    public function testConstructor()
    {
        $this->assertSame(2, $this->editor->bufferSize());
    }

    public function testConstructorDefaults()
    {
        $this->editor = new StreamEditor;

        $this->assertSame(8192, $this->editor->bufferSize());
    }

    public function replaceData()
    {
        //                                   original     offset size replacement  expected           delta
        return array(
            'Expansion at start'    => array('123456789', 0,     3,   'ABCDEFGHI', 'ABCDEFGHI456789', 6),
            'Expansion at middle'   => array('123456789', 3,     3,   'ABCDEFGHI', '123ABCDEFGHI789', 6),
            'Expansion at end'      => array('123456789', 6,     3,   'ABCDEFGHI', '123456ABCDEFGHI', 6),

            'Contraction at start'  => array('123456789', 0,     6,   'AB',        'AB789',           -4),
            'Contraction at middle' => array('123456789', 2,     6,   'AB',        '12AB9',           -4),
            'Contraction at end'    => array('123456789', 3,     6,   'AB',        '123AB',           -4),

            'Deletion at start'     => array('123456789', 0,     6,   null,        '789',             -6),
            'Deletion at middle'    => array('123456789', 2,     6,   null,        '129',             -6),
            'Deletion at end'       => array('123456789', 3,     6,   null,        '123',             -6),
        );
    }

    /**
     * @dataProvider replaceData
     */
    public function testReplace($original, $offset, $size, $replacement, $expected, $delta)
    {
        fwrite($this->stream, $original);
        $actualDelta = $this->editor->replace($this->stream, $offset, $size, $replacement);
        fseek($this->stream, 0);
        $actual = stream_get_contents($this->stream);

        $this->assertSame(implode("\n", str_split($expected)), implode("\n", str_split($actual)));
        $this->assertSame($delta, $actualDelta);
    }

    public function testReplaceMultiple()
    {
        $original = '123456789';
        $replacements = array(
            array(0, 2, 'ABCDEF'),
            array(7, 2, 'GHI'),
            array(7, 1, 'JK'),
            array(2, 3, 'LM'),
        );
        $expected = 'ABCDEFLM67GHI9';
        fwrite($this->stream, $original);
        $actualDelta = $this->editor->replaceMultiple($this->stream, $replacements);
        fseek($this->stream, 0);
        $actual = stream_get_contents($this->stream);

        $this->assertSame(implode("\n", str_split($expected)), implode("\n", str_split($actual)));
        $this->assertSame(5, $actualDelta);
    }

    public function testInstance()
    {
        $class = get_class($this->editor);
        $liberatedClass = Liberator::liberateClass($class);
        $liberatedClass->instance = null;
        $actual = $class::instance();

        $this->assertInstanceOf($class, $actual);
        $this->assertSame($actual, $class::instance());
    }
}
