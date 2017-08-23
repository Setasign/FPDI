<?php

namespace setasign\Fpdi\functional\PdfParser\Type;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\PdfParser\StreamReader;
use setasign\Fpdi\PdfParser\Tokenizer;
use setasign\Fpdi\PdfParser\Type\PdfName;

class PdfNameTest extends TestCase
{
    public function parseProvider()
    {
        $data = [
            [
                'Name',
                PdfName::create('Name')
            ],
            [
                ' ',
                PdfName::create('')
            ],
            [
                '',
                PdfName::create('')
            ],
            [
                ' Token',
                PdfName::create('')
            ],
            [
                '#23Test',
                PdfName::create('#23Test')
            ],
            [
                '#23Test%abc',
                PdfName::create('#23Test')
            ],
            [
                "ABC\nC",
                PdfName::create('ABC')
            ],
            [
                'ABC\nC',
                PdfName::create('ABC\nC')
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
        $tokenizer = new Tokenizer($stream);

        $result = PdfName::parse($tokenizer, $stream);

        $this->assertEquals($expectedResult, $result);
    }
}
