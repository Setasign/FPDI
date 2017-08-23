<?php

namespace setasign\Fpdi\functional\PdfParser\Type;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\PdfParser\PdfParser;
use setasign\Fpdi\PdfParser\StreamReader;
use setasign\Fpdi\PdfParser\Type\PdfArray;
use setasign\Fpdi\PdfParser\Type\PdfNumeric;
use setasign\Fpdi\PdfParser\Type\PdfString;

class PdfArrayTest extends TestCase
{
    public function parseProvider()
    {
        $data = [
            [
                '(hello) (world)]',
                [
                    PdfString::create('hello'),
                    PdfString::create('world')
                ]
            ],
            [
                '(hello) [(this is a test)] (world)]',
                [
                    PdfString::create('hello'),
                    PdfArray::create([
                        PdfString::create('this is a test')
                    ]),
                    PdfString::create('world'),
                ]
            ],
            [
                '(a) [(b) [(c) [(d)](e)]]]',
                [
                    PdfString::create('a'),
                    PdfArray::create([
                        PdfString::create('b'),
                        PdfArray::create([
                            PdfString::create('c'),
                            PdfArray::create([
                                PdfString::create('d')
                            ]),
                            PdfString::create('e')
                        ])
                    ])
                ]
            ],
            [
                "123 %test\n456]",
                [
                    PdfNumeric::create(123),
                    PdfNumeric::create(456)
                ]
            ],
            [
                "%test\n123 456]",
                [
                    PdfNumeric::create(123),
                    PdfNumeric::create(456)
                ]
            ],
            [
                "123 456%test\n%test\n]",
                [
                    PdfNumeric::create(123),
                    PdfNumeric::create(456)
                ]
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
        $parser = new PdfParser($stream);
        $tokenizer = $parser->getTokenizer();

        $result = PdfArray::parse($tokenizer, $parser);

        $this->assertInstanceOf(PdfArray::class, $result);
        $this->assertEquals($expectedResult, $result->value);
    }

    public function testParseWithEndingStream()
    {
        $stream = StreamReader::createByString('(Hallo Welt)');
        $parser = new PdfParser($stream);
        $tokenizer = $parser->getTokenizer();

        $result = PdfArray::parse($tokenizer, $parser);

        $this->assertFalse($result);
    }
}
