<?php

namespace setasign\Fpdi\functional\PdfParser\Type;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\PdfParser\StreamReader;
use setasign\Fpdi\PdfParser\Tokenizer;
use setasign\Fpdi\PdfParser\Type\PdfHexString;

class PdfHexStringTest extends TestCase
{
    public function parseProvider()
    {
        $data = [
            [
                '53657461504446>',
                PdfHexString::create('53657461504446')
            ],
            [
                '53 65 74 61 50 44 46>',
                PdfHexString::create('53 65 74 61 50 44 46')
            ],
            [
                '5>',
                PdfHexString::create('5')
            ],
            [
                str_repeat('01', 150) . '>',
                PdfHexString::create(str_repeat('01', 150))
            ]
        ];

        return $data;
    }

    /**
     * @param $in
     * @param $expectedResult
     * @dataProvider parseProvider
     */
    public function testParse($in, $expectedResult)
    {
        $stream = StreamReader::createByString($in);
        $result = PdfHexString::parse($stream);
        $this->assertInstanceOf(PdfHexString::class, $result);

        $this->assertSame($expectedResult->value, $result->value);
    }

    public function testParseWithEndingStream()
    {
        $stream = StreamReader::createByString('040815162342');
        $result = PdfHexString::parse($stream);

        $this->assertFalse($result);
    }
}
