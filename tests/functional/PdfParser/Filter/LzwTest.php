<?php

namespace setasign\Fpdi\functional\PdfParser\Filter;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\PdfParser\Filter\Lzw;

class LzwTest extends TestCase
{
    public function decodeProvider()
    {
        return [
            [
                "\x80\x0B\x60\x50\x22\x0C\x0C\x85\x01",
                '-----A---B'
            ],
            [
                "\x80\x0D\x47\x23\x91\x00\xC2\x0A\x20\x1C\x0D\x06\x30\x78\x31\x8C\xDA\x0A\x17\x92"
                . "\x4D\xA6\x78\x59\x10\xDF\x01",
                "599 0 0 841 0 0 cm\n/Img1 Do"
            ]
        ];
    }

    /**
     * @dataProvider decodeProvider
     */
    public function testDecode($in, $expected)
    {
        $filter = new Lzw();

        $decoded = $filter->decode($in);

        $this->assertEquals($expected, $decoded);
    }

    /**
     * @expectedException \setasign\Fpdi\PdfParser\Filter\LzwException
     * @expectedExceptionCode \setasign\Fpdi\PdfParser\Filter\LzwException::LZW_FLAVOUR_NOT_SUPPORTED
     */
    public function testDecodeWithLZWflavour()
    {
        $filter = new Lzw();

        $filter->decode(0x00 . 0x01);
    }
}