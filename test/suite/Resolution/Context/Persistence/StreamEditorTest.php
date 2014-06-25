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
        $this->error = array(
            'message' => 'Error message.',
            'type' => E_WARNING,
            'file' => '/path/to/file',
            'line' => 111,
        );
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
        //                                                   original     offset size  replacement  expected           delta
        return array(
            'Insertion at start'                    => array('123456789', 0,     0,    'ABC',       'ABC123456789',    3),
            'Insertion at middle'                   => array('123456789', 5,     0,    'ABC',       '12345ABC6789',    3),
            'Insertion at end'                      => array('123456789', 9,     0,    'ABC',       '123456789ABC',    3),

            'Replacement at start'                  => array('123456789', 0,     3,    'ABC',       'ABC456789',       0),
            'Replacement at middle'                 => array('123456789', 3,     3,    'ABC',       '123ABC789',       0),
            'Replacement at end'                    => array('123456789', 6,     3,    'ABC',       '123456ABC',       0),

            'Expansion at start'                    => array('123456789', 0,     3,    'ABCDEFGHI', 'ABCDEFGHI456789', 6),
            'Expansion at middle'                   => array('123456789', 3,     3,    'ABCDEFGHI', '123ABCDEFGHI789', 6),
            'Expansion at end'                      => array('123456789', 6,     3,    'ABCDEFGHI', '123456ABCDEFGHI', 6),

            'Contraction at start'                  => array('123456789', 0,     6,    'AB',        'AB789',           -4),
            'Contraction at middle'                 => array('123456789', 2,     6,    'AB',        '12AB9',           -4),
            'Contraction at end'                    => array('123456789', 3,     6,    'AB',        '123AB',           -4),

            'Deletion at start'                     => array('123456789', 0,     6,    null,        '789',             -6),
            'Deletion at middle'                    => array('123456789', 2,     6,    null,        '129',             -6),
            'Deletion at end'                       => array('123456789', 3,     6,    null,        '123',             -6),

            'Truncation at start'                   => array('123456789', 0,     null, 'ABC',       'ABC',             -6),
            'Truncation at middle'                  => array('123456789', 5,     null, 'ABC',       '12345ABC',        -1),
            'Truncation at end'                     => array('123456789', 9,     null, 'ABC',       '123456789ABC',    3),

            'Truncation at start, no replacement'   => array('123456789', 0,     null, null,        '',                -9),
            'Truncation at middle, no replacement'  => array('123456789', 5,     null, null,        '12345',           -4),
            'Truncation at end, no replacement'     => array('123456789', 9,     null, null,        '123456789',       0),
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

    public function testReplaceFailureUndefinedOffsetBeforeStart()
    {
        $this->setExpectedException('Eloquent\Cosmos\Resolution\Context\Persistence\Exception\StreamOffsetOutOfBoundsException');
        $this->editor->replace($this->stream, -1);
    }

    public function testReplaceFailureUndefinedOffsetAfterEnd()
    {
        $this->setExpectedException('Eloquent\Cosmos\Resolution\Context\Persistence\Exception\StreamOffsetOutOfBoundsException');
        $this->editor->replace($this->stream, 1);
    }

    public function testReplaceFailureStreamNotSeekable()
    {
        Phake::when($this->isolator)->stream_get_meta_data(Phake::anyParameters())->thenReturn(array('seekable' => false));

        $this->setExpectedException('Eloquent\Cosmos\Exception\ReadException');
        $this->editor->replace($this->stream, 0);
    }

    public function testReplaceFailureGetMetaData()
    {
        Phake::when($this->isolator)->stream_get_meta_data(Phake::anyParameters())->thenReturn(false);
        Phake::when($this->isolator)->error_get_last()->thenReturn($this->error);

        $this->setExpectedException('Eloquent\Cosmos\Exception\ReadException', 'Unable to read from stream: Error message.');
        $this->editor->replace($this->stream, 0);
    }

    public function testReplaceFailureSeek()
    {
        Phake::when($this->isolator)->fseek(Phake::anyParameters())->thenReturn(0)->thenReturn(false);
        Phake::when($this->isolator)->error_get_last()->thenReturn($this->error);

        $this->setExpectedException('Eloquent\Cosmos\Exception\ReadException', 'Unable to read from stream: Error message.');
        $this->editor->replace($this->stream, 0);
    }

    public function testReplaceFailureTell()
    {
        Phake::when($this->isolator)->ftell(Phake::anyParameters())->thenReturn(false);
        Phake::when($this->isolator)->error_get_last()->thenReturn($this->error);

        $this->setExpectedException('Eloquent\Cosmos\Exception\ReadException', 'Unable to read from stream: Error message.');
        $this->editor->replace($this->stream, 0);
    }

    public function testReplaceFailureRead()
    {
        fwrite($this->stream, '123456789');
        Phake::when($this->isolator)->fread(Phake::anyParameters())->thenReturn(false);
        Phake::when($this->isolator)->error_get_last()->thenReturn($this->error);

        $this->setExpectedException('Eloquent\Cosmos\Exception\ReadException', 'Unable to read from stream: Error message.');
        $this->editor->replace($this->stream, 0);
    }

    public function testReplaceFailureReadNoLastError()
    {
        fwrite($this->stream, '123456789');
        Phake::when($this->isolator)->fread(Phake::anyParameters())->thenReturn(false);
        Phake::when($this->isolator)->error_get_last()->thenReturn(null);

        $this->setExpectedException('Eloquent\Cosmos\Exception\ReadException', 'Unable to read from stream.');
        $this->editor->replace($this->stream, 0);
    }

    public function testReplaceFailureWrite()
    {
        fwrite($this->stream, '123456789');
        Phake::when($this->isolator)->fwrite(Phake::anyParameters())->thenReturn(false);
        Phake::when($this->isolator)->error_get_last()->thenReturn($this->error);

        $this->setExpectedException('Eloquent\Cosmos\Exception\WriteException', 'Unable to write to stream: Error message.');
        $this->editor->replace($this->stream, 0);
    }

    public function testReplaceFailureTruncate()
    {
        fwrite($this->stream, '123456789');
        Phake::when($this->isolator)->ftruncate(Phake::anyParameters())->thenReturn(false);
        Phake::when($this->isolator)->error_get_last()->thenReturn($this->error);

        $this->setExpectedException('Eloquent\Cosmos\Exception\WriteException', 'Unable to write to stream: Error message.');
        $this->editor->replace($this->stream, 0);
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
        $expected = 'ABCDEFLM67JKHI';
        fwrite($this->stream, $original);
        $actualDelta = $this->editor->replaceMultiple($this->stream, $replacements);
        fseek($this->stream, 0);
        $actual = stream_get_contents($this->stream);

        $this->assertSame(implode("\n", str_split($expected)), implode("\n", str_split($actual)));
        $this->assertSame(5, $actualDelta);
    }

    public function testFindIndentByOffset()
    {
        $data = "foo\n\n    bar\n\n        baz\n";
        fwrite($this->stream, $data);

        $this->assertSame(bin2hex(''), bin2hex($this->editor->findIndentByOffset($this->stream, 0)));
        $this->assertSame(bin2hex(''), bin2hex($this->editor->findIndentByOffset($this->stream, 1)));
        $this->assertSame(bin2hex(''), bin2hex($this->editor->findIndentByOffset($this->stream, 4)));
        $this->assertSame(bin2hex('    '), bin2hex($this->editor->findIndentByOffset($this->stream, 5)));
        $this->assertSame(bin2hex('    '), bin2hex($this->editor->findIndentByOffset($this->stream, 12)));
        $this->assertSame(bin2hex(''), bin2hex($this->editor->findIndentByOffset($this->stream, 13)));
        $this->assertSame(bin2hex('        '), bin2hex($this->editor->findIndentByOffset($this->stream, 14)));
        $this->assertSame(bin2hex('        '), bin2hex($this->editor->findIndentByOffset($this->stream, 25)));
        $this->assertSame(bin2hex(''), bin2hex($this->editor->findIndentByOffset($this->stream, 26)));
    }

    public function testFindIndentByOffsetWithTabs()
    {
        $data = "foo\n\n\t\t\t\tbar\n\n\t\t\t\t\t\t\t\tbaz\n";
        fwrite($this->stream, $data);

        $this->assertSame(bin2hex(''), bin2hex($this->editor->findIndentByOffset($this->stream, 0)));
        $this->assertSame(bin2hex(''), bin2hex($this->editor->findIndentByOffset($this->stream, 1)));
        $this->assertSame(bin2hex(''), bin2hex($this->editor->findIndentByOffset($this->stream, 4)));
        $this->assertSame(bin2hex("\t\t\t\t"), bin2hex($this->editor->findIndentByOffset($this->stream, 5)));
        $this->assertSame(bin2hex("\t\t\t\t"), bin2hex($this->editor->findIndentByOffset($this->stream, 12)));
        $this->assertSame(bin2hex(''), bin2hex($this->editor->findIndentByOffset($this->stream, 13)));
        $this->assertSame(bin2hex("\t\t\t\t\t\t\t\t"), bin2hex($this->editor->findIndentByOffset($this->stream, 14)));
        $this->assertSame(bin2hex("\t\t\t\t\t\t\t\t"), bin2hex($this->editor->findIndentByOffset($this->stream, 25)));
        $this->assertSame(bin2hex(''), bin2hex($this->editor->findIndentByOffset($this->stream, 26)));
    }

    public function testFindIndentByOffsetWithMixedIndentation()
    {
        $data = "foo\n\n \t  bar\n\n  \t  \t  baz\n";
        fwrite($this->stream, $data);

        $this->assertSame(bin2hex(''), bin2hex($this->editor->findIndentByOffset($this->stream, 0)));
        $this->assertSame(bin2hex(''), bin2hex($this->editor->findIndentByOffset($this->stream, 1)));
        $this->assertSame(bin2hex(''), bin2hex($this->editor->findIndentByOffset($this->stream, 4)));
        $this->assertSame(bin2hex(" \t  "), bin2hex($this->editor->findIndentByOffset($this->stream, 5)));
        $this->assertSame(bin2hex(" \t  "), bin2hex($this->editor->findIndentByOffset($this->stream, 12)));
        $this->assertSame(bin2hex(''), bin2hex($this->editor->findIndentByOffset($this->stream, 13)));
        $this->assertSame(bin2hex("  \t  \t  "), bin2hex($this->editor->findIndentByOffset($this->stream, 14)));
        $this->assertSame(bin2hex("  \t  \t  "), bin2hex($this->editor->findIndentByOffset($this->stream, 25)));
        $this->assertSame(bin2hex(''), bin2hex($this->editor->findIndentByOffset($this->stream, 26)));
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
