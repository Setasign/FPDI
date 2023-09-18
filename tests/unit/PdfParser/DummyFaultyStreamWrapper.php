<?php

namespace setasign\Fpdi\unit\PdfParser;

/**
 * This stream wrapper is created to simulate a stream wrapper that is not seekable (while its metadata report that
 * it is seekable).
 */
class DummyFaultyStreamWrapper
{
    public $context;

    function stream_open($path, $mode, $options, &$opened_path)
    {
        return true;
    }

    function stream_read($count)
    {
        return '';
    }

    function stream_write($data)
    {
        return 0;
    }

    function stream_tell()
    {
        return 0;
    }

    function stream_eof()
    {
        return false;
    }

    function stream_seek($offset, $whence)
    {
        return false;
    }

    function stream_metadata($path, $option, $var)
    {
        return true;
    }

    function stream_stat()
    {
        return [];
    }
}
