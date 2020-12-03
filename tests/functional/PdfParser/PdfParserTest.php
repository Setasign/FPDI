<?php

namespace setasign\Fpdi\functional\PdfParser;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException;
use setasign\Fpdi\PdfParser\PdfParser;
use setasign\Fpdi\PdfParser\PdfParserException;
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
use setasign\Fpdi\PdfParser\Type\PdfTypeException;

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

    public function readValueWithExpectedTypeProvider()
    {
        return [
            [
                "123",
                PdfNumeric::class,
                PdfNumeric::create(123)
            ],
            [
                "true",
                PdfBoolean::class,
                PdfBoolean::create(true)
            ],
            [
                "false",
                PdfBoolean::class,
                PdfBoolean::create(false)
            ],
            [
                "null",
                PdfNull::class,
                new PdfNull()
            ],
            [
                "any",
                PdfToken::class,
                PdfToken::create('any')
            ],
            [
                "1 0 R",
                PdfIndirectObjectReference::class,
                PdfIndirectObjectReference::create(1, 0)
            ],
            [
                "1 0 obj 1",
                PdfIndirectObject::class,
                PdfIndirectObject::create(1, 0, PdfNumeric::create(1))
            ],
            [
                "[1]",
                PdfArray::class,
                PdfArray::create([PdfNumeric::create(1)])
            ],
            [
                "<FA>",
                PdfHexString::class,
                PdfHexString::create('FA')
            ],
            [
                "/Name",
                PdfName::class,
                PdfName::create('Name')
            ],
            [
                "<</Name/value>>",
                PdfDictionary::class,
                PdfDictionary::create(['Name' => PdfName::create('value')])
            ],
            [
                "(String)",
                PdfString::class,
                PdfString::create('String')
            ]
        ];
    }

    /**
     * @param $in
     * @param $expectedType
     * @param $expectedResult
     * @throws \setasign\Fpdi\PdfParser\Type\PdfTypeException
     * @dataProvider readValueWithExpectedTypeProvider
     */
    public function testReadValueWithExpectedType($in, $expectedType, $expectedResult)
    {
        $stream = StreamReader::createByString($in);
        $parser = new PdfParser($stream);
        $result = $parser->readValue(null, $expectedType);
        $this->assertEquals($expectedResult, $result);
    }

    public function readValueWithInvalidTypeProvider()
    {
        return [
            [
                '(string)',
                PdfName::class
            ],
            [
                'Anything',
                PdfName::class
            ],
            [
                '[123]',
                PdfName::class
            ],
            [
                '123',
                PdfName::class
            ],
            [
                '/Name',
                PdfNumeric::class
            ],
            [
                '<<>>',
                PdfName::class
            ],
            [
                '<FA>',
                PdfName::class
            ],
            [
                '1 0 obj',
                PdfName::class
            ],
            [
                '1 0 R',
                PdfName::class
            ],
        ];
    }

    /**
     * @param $in
     * @param $expectedType
     * @throws \setasign\Fpdi\PdfParser\Type\PdfTypeException
     * @dataProvider readValueWithInvalidTypeProvider
     */
    public function testReadValueWithInvalidType($in, $expectedType)
    {
        $stream = StreamReader::createByString($in);
        $parser = new PdfParser($stream);
        $this->expectException(PdfTypeException::class);
        $this->expectExceptionCode(PdfTypeException::INVALID_DATA_TYPE);
        $parser->readValue(null, $expectedType);
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

    public function testGetPdfVersionOnLargeNonPdfDocument()
    {
        $pdf = str_repeat('This is not a PDF ', 100000);
        $reader = StreamReader::createByString($pdf);
        $parser = new PdfParser($reader);
        $this->expectException(PdfParserException::class);
        $this->expectExceptionCode(PdfParserException::FILE_HEADER_NOT_FOUND);
        $parser->getPdfVersion();
    }

    public function testGetPdfVersionWithVersionInCatalog()
    {
        $pdf = "%PDF-1.4\n"
            . "%% anything\n"
            . "1 0 obj\n"
            . "<</Version /1#2E5>>\n" // we use an escaped sequence to test this behaviour as well
            . "endobj\n"
            . "xref\n"
            . "1 1\n"
            . "0000000021 00000 n \n"
            . "trailer\n"
            . "<</Root 1 0 R>>\n"
            . "startxref\n"
            . "56\n"
            . "%%EOF";

        $reader = StreamReader::createByString($pdf);
        $parser = new PdfParser($reader);

        $this->assertSame([1, 5], $parser->getPdfVersion());
    }

    /**
     * This document has objects hidden through a compressed cross-reference
     */
    public function testGetIndirectObjectOnHybridFile()
    {
        $parser = new PdfParser(StreamReader::createByFile(__DIR__ . '/../../_files/pdfs/HybridFile.pdf'));

        $this->expectException(CrossReferenceException::class);
        $this->expectExceptionCode(CrossReferenceException::OBJECT_NOT_FOUND);
        $parser->getIndirectObject(25);
    }

    public function testDocumentWithBytesBeforeFileHeader()
    {
        $parser = new PdfParser(StreamReader::createByFile(
            __DIR__ . '/../../_files/pdfs/specials/bytes-before-file-header/'
            . 'Fantastic-Speaker-bytes-before-fileheader.pdf'
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

    /**
     * If we wouldn't expect a specific object type when resolving an indirect object, this test would end in a try to
     * build a recursive array with a depth of more than 15.000.000 which would end in a memory problem.
     *
     * @throws PdfParserException
     */
    public function testGetIndirectObjectWithInvalidType()
    {
        $parser = new PdfParser(StreamReader::createByFile(
            __DIR__ . '/../../_files/pdfs/specials/invalid-type-at-object-offset.pdf'
        ));

        $this->expectException(PdfParserException::class);
        $assertionMethodName = (
            \method_exists($this, 'expectExceptionMessageMatches')
            ? 'expectExceptionMessageMatches'
            : 'expectExceptionMessageRegExp'
        );
        $this->$assertionMethodName('/Got unexpected token type/');
        try {
            $parser->getIndirectObject(6);
        } catch (CrossReferenceException $e) {
            $this->assertSame(CrossReferenceException::OBJECT_NOT_FOUND, $e->getCode());
            throw $e->getPrevious();
        }
    }
}
