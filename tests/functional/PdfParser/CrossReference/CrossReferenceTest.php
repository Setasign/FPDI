<?php

namespace setasign\Fpdi\functional\PdfParser;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\PdfParser\CrossReference\CrossReference;
use setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException;
use setasign\Fpdi\PdfParser\PdfParser;
use setasign\Fpdi\PdfParser\StreamReader;
use setasign\Fpdi\PdfParser\Type\PdfArray;
use setasign\Fpdi\PdfParser\Type\PdfBoolean;
use setasign\Fpdi\PdfParser\Type\PdfDictionary;
use setasign\Fpdi\PdfParser\Type\PdfHexString;
use setasign\Fpdi\PdfParser\Type\PdfIndirectObject;
use setasign\Fpdi\PdfParser\Type\PdfIndirectObjectReference;
use setasign\Fpdi\PdfParser\Type\PdfName;
use setasign\Fpdi\PdfParser\Type\PdfNumeric;
use setasign\Fpdi\PdfParser\Type\PdfStream;
use setasign\Fpdi\PdfParser\Type\PdfString;

class CrossReferenceTest extends TestCase
{
    public function handlingProvider()
    {
        $data = [];

        $data[] = [
            "xref\n" .
            "0 5\r\n" .
            "0000000000 65535 f\r\n" .
            "0000001000 00000 n\r\n" .
            "0000002000 00000 n\r\n" .
            "0000003000 00000 n\r\n" .
            "0000004000 00000 n\r\n" .
            "trailer\n" .
            "<</Size 5 /Root 1 0 R>>\n" .
            "startxref\n" .
            "0\n" .
            "%%EOF",
            PdfDictionary::create([
                'Size' => PdfNumeric::create(5),
                'Root' => PdfIndirectObjectReference::create(1, 0)
            ]),
            [
                1 => 1000,
                2 => 2000,
                3 => 3000,
                4 => 4000
            ]
        ];

        $data[] = [
            "xref\n" .
            "0 5\r\n" .
            "0000000000 65535 f\r\n" .
            "0000001000 00000 n\r\n" .
            "0000002000 00000 n\r\n" .
            "0000003000 00000 n\r\n" .
            "0000004000 00000 n\r\n" .
            "trailer\n" .
            "<</Size 5 /Root 1 0 R>>\n" .
            "startref\n" . // <-------- without "x"
            "0\n" .
            "%%EOF",
            PdfDictionary::create([
                'Size' => PdfNumeric::create(5),
                'Root' => PdfIndirectObjectReference::create(1, 0)
            ]),
            [
                1 => 1000,
                2 => 2000,
                3 => 3000,
                4 => 4000
            ]
        ];

        $data[] = [
            "xref\n" .
            "1 5\r\n" . // <--- faulty subsection header
            "0000000000 65535 f\r\n" .
            "0000001000 00000 n\r\n" .
            "0000002000 00000 n\r\n" .
            "0000003000 00000 n\r\n" .
            "0000004000 00000 n\r\n" .
            "trailer\n" .
            "<</Size 5 /Root 1 0 R>>\n" .
            "startxref\n" .
            "0\n" .
            "%%EOF",
            PdfDictionary::create([
                'Size' => PdfNumeric::create(5),
                'Root' => PdfIndirectObjectReference::create(1, 0)
            ]),
            [
                1 => 1000,
                2 => 2000,
                3 => 3000,
                4 => 4000
            ]
        ];

        $data[] = [
            "xref\n" .
            "0 3\r\n" .
            "0000000000 65535 f\r\n" .
            "0000001000 00000 n\r\n" .
            "0000002000 00000 n\r\n" .
            "trailer\n" .
            "<</Size 3 /Root 1 0 R>>\n" .
            "startxref\n" .
            "0\n" .
            "%%EOF\n" .
            "xref\n" .
            "3 2\n" .
            "0000003000 00000 n\r\n" .
            "0000004000 00000 n\r\n" .
            "trailer\n" .
            "<</Size 5 /Prev 0 /Root 1 0 R>>\n" .
            "startxref\n" .
            "120\n" .
            "%%EOF\n",
            PdfDictionary::create([
                'Size' => PdfNumeric::create(5),
                'Prev' => PdfNumeric::create(0),
                'Root' => PdfIndirectObjectReference::create(1, 0)
            ]),
            [
                1 => 1000,
                2 => 2000,
                3 => 3000,
                4 => 4000
            ]
        ];

        $data[] = [
            "xref\n" .
            "0 3\r\n" .
            "0000000000 65535 f\r\n" .
            "0000001000 00000 n\r\n" .
            "0000002000 00000 n\r\n" .
            "trailer\n" .
            "<</Size 3 /Root 1 0 R>>\n" .
            "startxref\n" .
            "0\n" .
            "%%EOF\n" .
            "xref\n" .
            "1 2\n" .
            "0000001100 00000 n\r\n" .
            "0000002100 00000 n\r\n" .
            "trailer\n" .
            "<</Size 3 /Prev 0 /Root 1 0 R>>\n" .
            "startxref\n" .
            "120\n" .
            "%%EOF\n",
            PdfDictionary::create([
                'Size' => PdfNumeric::create(3),
                'Prev' => PdfNumeric::create(0),
                'Root' => PdfIndirectObjectReference::create(1, 0)
            ]),
            [
                1 => 1100,
                2 => 2100
            ]
        ];

        $data[] = [
            "xref\n" .
            "1 3\r\n" . // faulty subsection header but handled as it is, because there are more tables.
            "0000000000 65535 f\r\n" .
            "0000001000 00000 n\r\n" .
            "0000002000 00000 n\r\n" .
            "trailer\n" .
            "<</Size 3 /Root 1 0 R>>\n" .
            "startxref\n" .
            "0\n" .
            "%%EOF\n" .
            "xref\n" .
            "2 2\n" .
            "0000001100 00000 n\r\n" .
            "0000002100 00000 n\r\n" .
            "trailer\n" .
            "<</Size 3 /Prev 0 /Root 1 0 R>>\n" .
            "startxref\n" .
            "120\n" .
            "%%EOF\n",
            PdfDictionary::create([
                'Size' => PdfNumeric::create(3),
                'Prev' => PdfNumeric::create(0),
                'Root' => PdfIndirectObjectReference::create(1, 0)
            ]),
            [
                1 => 1000,
                2 => 1100,
                3 => 2100
            ]
        ];

        $data[] = [
            "endobj\n" .
            "xref\n" .
            "0 18\n" .
            "0000000008 65535 f \n" .
            "0000000017 00000 n \n" .
            "0000000124 00000 n \n" .
            "0000000180 00000 n \n" .
            "0000000416 00000 n \n" .
            "0000000633 00000 n \n" .
            "0000000800 00000 n \n" .
            "0000001039 00000 n \n" .
            "0000000009 65535 f \n" .
            "0000000010 65535 f \n" .
            "0000000011 65535 f \n" .
            "0000000012 65535 f \n" .
            "0000000013 65535 f \n" .
            "0000000014 65535 f \n" .
            "0000000000 65535 f \n" .
            "0000001646 00000 n \n" .
            "0000001801 00000 n \n" .
            "0000081568 00000 n \n" .
            "trailer\n" .
            "<</Size 18/Root 1 0 R/Info 7 0 R/ID[<4D4F0C8F2B4F3F498F00EB925AE7B9D0><4D4F0C8F2B4F3F498F00EB925AE7B9D0>] >>\n" .
            "startxref\n" .
            "7\n" .
            "%%EOF\n" .
            "xref\n" .
            "0 0\n" .
            "trailer\n" .
            "<</Size 18/Root 1 0 R/Info 7 0 R/ID[<4D4F0C8F2B4F3F498F00EB925AE7B9D0><4D4F0C8F2B4F3F498F00EB925AE7B9D0>] /Prev 7/XRefStm 81568>>\n" .
            "startxref\n" .
            "512\n" .
            "%%EOF",
            PdfDictionary::create([
                'Size' => PdfNumeric::create(18),
                'Root' => PdfIndirectObjectReference::create(1, 0),
                'Info' => PdfIndirectObjectReference::create(7, 0),
                'ID' => PdfArray::create([
                    PdfHexString::create('4D4F0C8F2B4F3F498F00EB925AE7B9D0'),
                    PdfHexString::create('4D4F0C8F2B4F3F498F00EB925AE7B9D0'),
                ]),
                'Prev' => PdfNumeric::create(7),
                'XRefStm' => PdfNumeric::create(81568)
            ]),
            [
                1 => 17,
                2 => 124,
                6 => 800,
                9 => false,
                10 => false
            ]
        ];

        // test additional token after %%EOF
        $data[] = [
            "xref\n" .
            "0 5\r\n" .
            "0000000000 65535 f\r\n" .
            "0000001000 00000 n\r\n" .
            "0000002000 00000 n\r\n" .
            "0000003000 00000 n\r\n" .
            "0000004000 00000 n\r\n" .
            "trailer\n" .
            "<</Size 5 /Root 1 0 R>>\n" .
            "startxref\n" .
            "0\n" .
            "%%EOF\n" .
            "<let's confuse",
            PdfDictionary::create([
                'Size' => PdfNumeric::create(5),
                'Root' => PdfIndirectObjectReference::create(1, 0)
            ]),
            [
                1 => 1000,
                2 => 2000,
                3 => 3000,
                4 => 4000
            ]
        ];


        return $data;
    }

    /**
     * @param $string
     * @param $expectedTrailer
     * @param $expectedObjects
     * @dataProvider handlingProvider
     */
    public function testHandling($string, $expectedTrailer, $expectedObjects)
    {
        $stream = StreamReader::createByString($string);
        $parser = new PdfParser($stream);
        $xref = new CrossReference($parser);

        $trailer = $xref->getTrailer();
        $this->assertEquals($expectedTrailer, $trailer);

        foreach ($expectedObjects as $objectId => $position) {
            $this->assertSame($position, $xref->getOffsetFor($objectId));
        }
    }

    /**
     * @expectedException \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException
     * @expectedExceptionCode \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException::INVALID_DATA
     */
    public function testWithInvalidTokenAfterStartXrefKeyword()
    {
        $pdf = "startxref\nTOKEN";
        $stream = StreamReader::createByString($pdf);
        $parser = new PdfParser($stream);
        new CrossReference($parser);
    }

    /**
     * @expectedException \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException
     * @expectedExceptionCode \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException::INVALID_DATA
     */
    public function testWithInvalidPrevValue()
    {
        $pdf =
            "xref\n" .
            "0 3\r\n" .
            "0000000000 65535 f\r\n" .
            "0000001000 00000 n\r\n" .
            "0000002000 00000 n\r\n" .
            "trailer\n" .
            "<</Size 3 /Root 1 0 R>>\n" .
            "startxref\n" .
            "0\n" .
            "%%EOF\n" .
            "xref\n" .
            "1 2\n" .
            "0000001100 00000 n\r\n" .
            "0000002100 00000 n\r\n" .
            "trailer\n" .
            "<</Size 3 /Prev 10 /Root 1 0 R>>\n" .
            "startxref\n" .
            "120\n" .
            "%%EOF\n";

        $stream = StreamReader::createByString($pdf);
        $parser = new PdfParser($stream);
        new CrossReference($parser);
    }


    public function getIndirectObjectProvider()
    {
        $data = [];

        $path = __DIR__ . '/../../../_files/pdfs/';

        $data[] = [
            $path . 'Word2010.pdf',
            [
                1 => PdfIndirectObject::create(
                    1,
                    0,
                    PdfDictionary::create([
                        'Type' => PdfName::create('Catalog'),
                        'Pages' => PdfIndirectObjectReference::create(2, 0),
                        'Lang' => PdfString::create('de-DE'),
                        'StructTreeRoot' => PdfIndirectObjectReference::create(8, 0),
                        'MarkInfo' => PdfDictionary::create([
                            'Marked' => PdfBoolean::create(true)
                        ])
                    ])
                ),
                2 => PdfIndirectObject::create(
                    2,
                    0,
                    PdfDictionary::create([
                        'Type' => PdfName::create('Pages'),
                        'Count' => PdfNumeric::create(1),
                        'Kids' => PdfArray::create([
                            PdfIndirectObjectReference::create(3, 0)
                        ])
                    ])
                ),
                17 => PdfIndirectObject::create(
                    17,
                    0,
                    PdfStream::create(
                        PdfDictionary::create([
                            'Type' => PdfName::create('XRef'),
                            'Size' => PdfNumeric::create(17),
                            'W' => PdfArray::create([
                                PdfNumeric::create(1),
                                PdfNumeric::create(4),
                                PdfNumeric::create(2)
                            ]),
                            'Root' => PdfIndirectObjectReference::create(1, 0),
                            'Info' => PdfIndirectObjectReference::create(7, 0),
                            'ID' => PdfArray::create([
                                PdfHexString::create('4D4F0C8F2B4F3F498F00EB925AE7B9D0'),
                                PdfHexString::create('4D4F0C8F2B4F3F498F00EB925AE7B9D0')
                            ]),
                            'Filter' => PdfName::create('FlateDecode'),
                            'Length' => PdfNumeric::create(71)
                        ]),
                        'anything for testing'
                    )
                )
            ]
        ];

        $data[] = [
            $path . 'Boombastic-Box.pdf',
            [
                1 => PdfIndirectObject::create(
                    1,
                    0,
                    PdfDictionary::create([
                        'Nums' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfIndirectObjectReference::create(2, 0)
                        ])
                    ])
                ),

                2 => PdfIndirectObject::create(
                    2,
                    0,
                    PdfDictionary::create([
                        'S' => PdfName::create('D'),
                        'St' => PdfNumeric::create(9)
                    ])
                ),
                11 => PdfIndirectObject::create(
                    11,
                    0,
                    PdfStream::create(
                        PdfDictionary::create([
                            'Filter' => PdfName::create('FlateDecode'),
                            'Length' => PdfNumeric::create(1430)
                        ]),
                        'anything for testing'
                    )
                ),
                30 => PdfIndirectObject::create(
                    30,
                    0,
                    PdfStream::create(
                        PdfDictionary::create([
                            'Length' => PdfNumeric::create(4932),
                            'Subtype' => PdfName::create('XML'),
                            'Type' => PdfName::create('Metadata')
                        ]),
                        'anything for testing'
                    )
                )
            ]
        ];

        $data[] = [
            $path . 'filters/lzw/999998.pdf',
            [
                2 => PdfIndirectObject::create(
                    2,
                    0,
                    PdfStream::create(
                        PdfDictionary::create([
                            'Filter' => PdfName::create('LZWDecode'),
                            'Length' => PdfIndirectObjectReference::create(3, 0)
                        ]),
                        'anything for testing'
                    )
                )
            ]
        ];

        return $data;
    }

    /**
     * @param $filename
     * @param array $expectedResults
     * @dataProvider getIndirectObjectProvider
     */
    public function testGetIndirectObject($filename, array $expectedResults)
    {
        $stream = StreamReader::createByFile($filename);
        $parser = new PdfParser($stream);
        $xref = new CrossReference($parser);

        foreach ($expectedResults AS $objectId => $expectedResult) {
            $object = $xref->getIndirectObject($objectId);

            $this->assertEquals($expectedResult->objectNumber, $object->objectNumber);
            $this->assertEquals($expectedResult->generationNumber, $object->generationNumber);

            $this->assertSame(get_class($expectedResult->value), get_class($object->value));

            if ($object->value instanceof PdfStream) {
                $this->assertEquals($expectedResult->value->value, $object->value->value);
            } else {
                $this->assertEquals($expectedResult->value, $object->value);
            }
            #$this->assertEquals($expectedResult, $object);
        }

        $this->assertGreaterThan(0, $expectedResults);
    }

    /**
     * @expectedException \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException
     * @expectedExceptionCode \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException::COMPRESSED_XREF
     */
    public function testBehaviourOnCompressedXref()
    {
        $stream = StreamReader::createByFile(__DIR__ . '/../../../_files/pdfs/compressed-xref.pdf');
        $parser = new PdfParser($stream);
        new CrossReference($parser);
    }
}