<?php

namespace setasign\Fpdi\functional\PdfParser;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\PdfParser\PdfParser;
use setasign\Fpdi\PdfParser\StreamReader;
use setasign\Fpdi\PdfParser\Type\PdfArray;
use setasign\Fpdi\PdfParser\Type\PdfBoolean;
use setasign\Fpdi\PdfParser\Type\PdfDictionary;
use setasign\Fpdi\PdfParser\Type\PdfHexString;
use setasign\Fpdi\PdfParser\Type\PdfIndirectObject;
use setasign\Fpdi\PdfParser\Type\PdfIndirectObjectReference;
use setasign\Fpdi\PdfParser\Type\PdfName;
use setasign\Fpdi\PdfParser\Type\PdfNull;
use setasign\Fpdi\PdfParser\Type\PdfNumeric;
use setasign\Fpdi\PdfParser\Type\PdfString;
use setasign\Fpdi\PdfParser\Type\PdfToken;

class PdfParserTest extends TestCase
{
    public function readValueProvider()
    {
        $data = [
            [
                "123 true false(hello world)/Name[(value 1)(value 2)]",
                [
                    PdfNumeric::create(123),
                    PdfBoolean::create(true),
                    PdfBoolean::create(false),
                    PdfString::create('hello world'),
                    PdfName::create('Name'),
                    PdfArray::create([
                        PdfString::create('value 1'),
                        PdfString::create('value 2')
                    ])
                ]
            ],
            [
                '<F6F6>',
                [
                    PdfHexString::create('F6F6')
                ]
            ],
            [
                '<<>>',
                [
                    PdfDictionary::create([])
                ]
            ],
            [
                '<</Name(value)>><</AnotherName/Value>>',
                [
                    PdfDictionary::create([
                        'Name' => PdfString::create('value')
                    ]),
                    PdfDictionary::create([
                        'AnotherName' => PdfName::create('Value')
                    ])
                ]
            ],
            [
                "1,2",
                [
                    PdfToken::create('1,2')
                ]
            ],
            [
                "% a comment\n/Name",
                [
                    PdfName::create('Name')
                ]
            ],
            [
                "12 0 obj\n123\nendobj",
                [
                    PdfIndirectObject::create(
                        12,
                        0,
                        PdfNumeric::create(123)
                    )
                ]
            ],
            [
                '123 0 R',
                [
                    PdfIndirectObjectReference::create(123, 0)
                ]
            ],
            [
                'null',
                [
                    new PdfNull
                ]
            ],
            [
                '/hello /there%perfect',
                [
                    PdfName::create('hello'),
                    PdfName::create('there'),
                ]
            ],
            [
                "[1 2 3%comment\n]",
                [
                    PdfArray::create([
                        PdfNumeric::create(1),
                        PdfNumeric::create(2),
                        PdfNumeric::create(3),
                    ])
                ]
            ]
        ];

        return $data;
    }

    /**
     * @param $in
     * @param $expectedResults
     * @dataProvider readValueProvider
     */
    public function testReadValue($in, $expectedResults)
    {
        $stream = StreamReader::createByString($in);
        $parser = new PdfParser($stream);

        foreach ($expectedResults as $expectedResult) {
            $result = $parser->readValue();
            $this->assertEquals($expectedResult, $result);
        }
    }

    public function testGetPdfVersion()
    {
        $pdf = "%PDF-1.4\n"
            . "%% anything\n"
            . "1 0 obj\n"
            . "<</Pages 2 0 R>>\n"
            . "endobj\n"
            . "xref\n"
            . "1 1\n"
            . "0000000020 00000 n \n"
            . "trailer\n"
            . "<</Root 1 0 R>>\n"
            . "startxref\n"
            . "53\n"
            . "%%EOF";

        $reader = StreamReader::createByString($pdf);
        $parser = new PdfParser($reader);

        $this->assertSame([1, 4], $parser->getPdfVersion());
    }

    /**
     * @expectedException \setasign\Fpdi\PdfParser\PdfParserException
     * @expectedExceptionCode \setasign\Fpdi\PdfParser\PdfParserException::FILE_HEADER_NOT_FOUND
     */
    public function testGetPdfVersionOnLargeNonPdfDocument()
    {
        $pdf = str_repeat('This is not a PDF ', 100000);
        $reader = StreamReader::createByString($pdf);
        $parser = new PdfParser($reader);
        $parser->getPdfVersion();
    }

    public function testGetPdfVersionWithVersionInCatalog()
    {
        $pdf = "%PDF-1.4\n"
            . "%% anything\n"
            . "1 0 obj\n"
            . "<</Version /1.5>>\n"
            . "endobj\n"
            . "xref\n"
            . "1 1\n"
            . "0000000021 00000 n \n"
            . "trailer\n"
            . "<</Root 1 0 R>>\n"
            . "startxref\n"
            . "54\n"
            . "%%EOF";

        $reader = StreamReader::createByString($pdf);
        $parser = new PdfParser($reader);

        $this->assertSame([1, 5], $parser->getPdfVersion());
    }

    /**
     * This document has objects hidden through a compressed cross-reference
     *
     * @expectedException \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException
     * @expectedExceptionCode \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException::OBJECT_NOT_FOUND
     */
    public function testGetIndirectObjectOnHybridFile()
    {
        $parser = new PdfParser(StreamReader::createByFile( __DIR__ . '/../../_files/pdfs/HybridFile.pdf'));
        $parser->getIndirectObject(25);
    }

    public function testDocumentWithBytesBeforeFileHeader()
    {
        $parser = new PdfParser(StreamReader::createByFile(
            __DIR__ . '/../../_files/pdfs/specials/bytes-before-file-header/Fantastic-Speaker-bytes-before-fileheader.pdf'
        ));

        $object = $parser->getIndirectObject(6);
        $this->assertEquals(PdfIndirectObject::create(6, 0, PdfDictionary::create([
            'Linearized' => PdfNumeric::create(1),
            'L' => PdfNumeric::create(568673),
            'O' => PdfNumeric::create(8),
            'E' => PdfNumeric::create(563088),
            'N' => PdfNumeric::create(1),
            'T' => PdfNumeric::create(568434),
            'H' => PdfArray::create([
                PdfNumeric::create(1016),
                PdfNumeric::create(222)
            ])
        ])), $object);

        $this->assertEquals([1, 5], $parser->getPdfVersion());
    }
}