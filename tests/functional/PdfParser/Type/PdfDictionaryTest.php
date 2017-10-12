<?php

namespace setasign\Fpdi\functional\PdfParser\Type;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\PdfParser\PdfParser;
use setasign\Fpdi\PdfParser\StreamReader;
use setasign\Fpdi\PdfParser\Type\PdfDictionary;
use setasign\Fpdi\PdfParser\Type\PdfHexString;
use setasign\Fpdi\PdfParser\Type\PdfName;
use setasign\Fpdi\PdfParser\Type\PdfNumeric;
use setasign\Fpdi\PdfParser\Type\PdfString;

class PdfDictionaryTest extends TestCase
{
    public function parseProvider()
    {
        $data = [
            [
                '/A (hello) /B (world)>>',
                PdfDictionary::create([
                    'A' => PdfString::create('hello'),
                    'B' => PdfString::create('world'),
                ])
            ],
            [
                '/A (hello) B (world)>>',
                PdfDictionary::create([
                    'A' => PdfString::create('hello')
                ])
            ],
            [
                '/A (1) /B (2) /C <</D (4) /E (5)>> >>',
                PdfDictionary::create([
                    'A' => PdfString::create('1'),
                    'B' => PdfString::create('2'),
                    'C' => PdfDictionary::create([
                        'D' => PdfString::create('4'),
                        'E' => PdfString::create('5')
                    ])
                ])
            ],
            [
                '/Name null>>',
                PdfDictionary::create([])
            ],
            [
                '/Name>>',
                PdfDictionary::create([])
            ],
            [
                '>>',
                PdfDictionary::create([])
            ],
            // no name
            [
                '<<>>>>',
                PdfDictionary::create([])
            ],
            [
                '/A<<>>>>',
                PdfDictionary::create([
                    'A' => PdfDictionary::create([])
                ])
            ],
            [
                '/B<F6F6>>>',
                PdfDictionary::create([
                    'B' => PdfHexString::create('F6F6')
                ])
            ],
            [
                "%comment\n/A/B>>",
                PdfDictionary::create([
                    'A' => PdfName::create('B')
                ])
            ],
            [
                "/A%comment\n/B>>",
                PdfDictionary::create([
                    'A' => PdfName::create('B')
                ])
            ],
            [
                "/A/B%comment\n>>",
                PdfDictionary::create([
                    'A' => PdfName::create('B')
                ])
            ],
            [
                "/A/B%comment\n/C\n/D>>",
                PdfDictionary::create([
                    'A' => PdfName::create('B'),
                    'C' => PdfName::create('D')
                ])
            ],
            [
                "/A%comment\n%comment\n/B%comment\n%comment\n>>",
                PdfDictionary::create([
                    'A' => PdfName::create('B')
                ])
            ],
            // @todo
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

        $result = PdfDictionary::parse($tokenizer, $stream, $parser);

        $this->assertInstanceOf(PdfDictionary::class, $result);

        $this->assertEquals($expectedResult->value, $result->value);
    }

    public function testParseBehind()
    {
        // simulate <</A 123>><</B 321>>
        $stream = StreamReader::createByString('/A 123>>/B 321>>');
        $parser = new PdfParser($stream);
        $tokenizer = $parser->getTokenizer();

        $result = PdfDictionary::parse($tokenizer, $stream, $parser);

        $this->assertEquals(
            PdfDictionary::create([
                'A' => PdfNumeric::create(123)
            ]),
            $result
        );
        $result = PdfDictionary::parse($tokenizer, $stream, $parser);
        $this->assertEquals(
            PdfDictionary::create([
                'B' => PdfNumeric::create(321)
            ]),
            $result
        );
    }

    public function testParseWithEndingStream()
    {
        $stream = StreamReader::createByString('(Hallo Welt)');
        $parser = new PdfParser($stream);
        $tokenizer = $parser->getTokenizer();

        $result = PdfDictionary::parse($tokenizer, $stream, $parser);

        $this->assertFalse($result);


        $stream = StreamReader::createByString('/Name (Hallo Welt)');
        $parser = new PdfParser($stream);
        $tokenizer = $parser->getTokenizer();

        $result = PdfDictionary::parse($tokenizer, $stream, $parser);

        $this->assertFalse($result);


        $stream = StreamReader::createByString('/Name');
        $parser = new PdfParser($stream);
        $tokenizer = $parser->getTokenizer();

        $result = PdfDictionary::parse($tokenizer, $stream, $parser);

        $this->assertFalse($result);
    }
}
