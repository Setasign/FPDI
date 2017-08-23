<?php

namespace setasign\Fpdi\functional\PdfParser\Filter;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\PdfParser\Filter\AsciiHex;

class AsciiHexTest extends TestCase
{
    public function decodeProvider()
    {
        return [
            ['', ''],
            ["01 02 03\n \t04", "\x01\x02\x03\x04"],
            ["48616C6C6F", "Hallo"],
            ["48616c6c6f", "Hallo"],
            ["48 61 6C 6C 6F 20 57 65 6C 74 2", "Hallo Welt "]
        ];
    }

    /**
     * @dataProvider decodeProvider
     */
    public function testDecode($in, $expected)
    {
        $filter = new AsciiHex();
        $this->assertSame($expected, $filter->decode($in));
    }

    public function encodeProvider()
    {
        return [
            ['', ''],
            ["\x01\x02\x03\x04", "01020304"],
            ["Hallo", "48616c6c6f"],
            ["Hallo Welt ", "48616c6c6f2057656c7420"]
        ];
    }
    /**
     * @param $in
     * @param $expected
     * @dataProvider encodeProvider
     */
    public function testEncdoe($in, $expected)
    {
        $filter = new AsciiHex();
        $this->assertSame($expected, $filter->encode($in, true));
    }
}