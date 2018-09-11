<?php

namespace setasign\Fpdi\functional\PdfReader;

use PHPUnit\Framework\TestCase;
use Prophecy\Exception\InvalidArgumentException;
use setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException;
use setasign\Fpdi\PdfParser\PdfParser;
use setasign\Fpdi\PdfParser\StreamReader;
use setasign\Fpdi\PdfParser\Type\PdfArray;
use setasign\Fpdi\PdfParser\Type\PdfBoolean;
use setasign\Fpdi\PdfParser\Type\PdfDictionary;
use setasign\Fpdi\PdfParser\Type\PdfIndirectObject;
use setasign\Fpdi\PdfParser\Type\PdfIndirectObjectReference;
use setasign\Fpdi\PdfParser\Type\PdfName;
use setasign\Fpdi\PdfParser\Type\PdfNumeric;
use setasign\Fpdi\PdfParser\Type\PdfType;
use setasign\Fpdi\PdfReader\PdfReader;

class PdfReaderTest extends TestCase
{
    public function getPageCountProvider()
    {
        $data = [];
        $path = __DIR__ . '/../../_files/pdfs';

        $data[] = [
            $path . '/Boombastic-Box.pdf',
            1
        ];

        $data[] = [
            $path . '/filters/lzw/999998.pdf',
            10
        ];

        $data[] = [
            $path . '/Word2010.pdf',
            1
        ];

        $data[] = [
            $path . '/PageTree.pdf',
            10
        ];

        $data[] = [
            $path . '/PageTree2.pdf',
            13
        ];

        return $data;
    }

    /**
     * @param $path
     * @param $expectedCount
     * @dataProvider getPageCountProvider
     */
    public function testGetPageCount($path, $expectedCount)
    {
        $stream = StreamReader::createByFile($path);
        $parser = new PdfParser($stream);

        $pdfReader = new PdfReader($parser);

        $this->assertSame($expectedCount, $pdfReader->getPageCount());
    }

    public function getPageProvider()
    {
        $data = [];
        $path = __DIR__ . '/../../_files/pdfs';

        $data[] = [
            $path . '/filters/lzw/999998.pdf',
            [
                1 => PdfIndirectObject::create(
                    53,
                    0,
                    PdfDictionary::create([
                        'Type' => PdfName::create('Page'),
                        'MediaBox' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfNumeric::create(0),
                            PdfNumeric::create(660),
                            PdfNumeric::create(907)
                        ]),
                        'Parent' => PdfIndirectObjectReference::create(48, 0),
                        'Resources' => PdfDictionary::create([
                            'XObject' => PdfDictionary::create([
                                'Im1' => PdfIndirectObjectReference::create(57, 0)
                            ]),
                            'ProcSet' => PdfArray::create([
                                PdfName::create('PDF'),
                                PdfName::create('ImageB')
                            ])
                        ]),
                        'Contents' => PdfIndirectObjectReference::create(54, 0),
                        'CropBox' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfNumeric::create(0),
                            PdfNumeric::create(660),
                            PdfNumeric::create(907)
                        ]),
                        'Rotate' => PdfNumeric::create(0)
                    ])
                ),
                5 => PdfIndirectObject::create(
                    16,
                    0,
                    PdfDictionary::create([
                        'Type' => PdfName::create('Page'),
                        'MediaBox' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfNumeric::create(0),
                            PdfNumeric::create(660),
                            PdfNumeric::create(907)
                        ]),
                        'Parent' => PdfIndirectObjectReference::create(48, 0),
                        'Resources' => PdfDictionary::create([
                            'XObject' => PdfDictionary::create([
                                'Im1' => PdfIndirectObjectReference::create(20, 0)
                            ]),
                            'ProcSet' => PdfArray::create([
                                PdfName::create('PDF'),
                                PdfName::create('ImageB')
                            ])
                        ]),
                        'Contents' => PdfIndirectObjectReference::create(17, 0),
                        'CropBox' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfNumeric::create(0),
                            PdfNumeric::create(660),
                            PdfNumeric::create(907)
                        ]),
                        'Rotate' => PdfNumeric::create(0)
                    ])
                ),
                7 => PdfIndirectObject::create(
                    26,
                    0,
                    PdfDictionary::create([
                        'Type' => PdfName::create('Page'),
                        'MediaBox' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfNumeric::create(0),
                            PdfNumeric::create(660),
                            PdfNumeric::create(907)
                        ]),
                        'Parent' => PdfIndirectObjectReference::create(50, 0),
                        'Resources' => PdfDictionary::create([
                            'XObject' => PdfDictionary::create([
                                'Im1' => PdfIndirectObjectReference::create(30, 0)
                            ]),
                            'ProcSet' => PdfArray::create([
                                PdfName::create('PDF'),
                                PdfName::create('ImageB')
                            ])
                        ]),
                        'Contents' => PdfIndirectObjectReference::create(27, 0),
                        'CropBox' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfNumeric::create(0),
                            PdfNumeric::create(660),
                            PdfNumeric::create(907)
                        ]),
                        'Rotate' => PdfNumeric::create(0)
                    ])
                ),
                10 => PdfIndirectObject::create(
                    41,
                    0,
                    PdfDictionary::create([
                        'Type' => PdfName::create('Page'),
                        'MediaBox' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfNumeric::create(0),
                            PdfNumeric::create(660),
                            PdfNumeric::create(907)
                        ]),
                        'Parent' => PdfIndirectObjectReference::create(50, 0),
                        'Resources' => PdfDictionary::create([
                            'XObject' => PdfDictionary::create([
                                'Im1' => PdfIndirectObjectReference::create(45, 0)
                            ]),
                            'ProcSet' => PdfArray::create([
                                PdfName::create('PDF'),
                                PdfName::create('ImageB')
                            ])
                        ]),
                        'Contents' => PdfIndirectObjectReference::create(42, 0),
                        'CropBox' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfNumeric::create(0),
                            PdfNumeric::create(660),
                            PdfNumeric::create(907)
                        ]),
                        'Rotate' => PdfNumeric::create(0)
                    ])
                )
            ]
        ];

        $data[] = [
            $path . '/PageTree.pdf',
            [
                1 => PdfIndirectObject::create(
                    4,
                    0,
                    PdfDictionary::create([
                        'Type' => PdfName::create('Page'),
                        'MediaBox' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfNumeric::create(0),
                            PdfNumeric::create(841.89),
                            PdfNumeric::create(595.28)
                        ]),
                        'Resources' => PdfDictionary::create([
                            'Font' => PdfDictionary::create([
                                'F1' => PdfIndirectObjectReference::create(3, 0)
                            ]),
                        ]),
                        'Contents' => PdfArray::create([
                            PdfIndirectObjectReference::create(5, 0)
                        ]),
                        'Parent' => PdfIndirectObjectReference::create(2, 0)
                    ])
                ),
                2 => PdfIndirectObject::create(
                    6,
                    0,
                    PdfDictionary::create([
                        'Type' => PdfName::create('Page'),
                        'MediaBox' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfNumeric::create(0),
                            PdfNumeric::create(595.28),
                            PdfNumeric::create(841.89)
                        ]),
                        'Resources' => PdfDictionary::create([
                            'Font' => PdfDictionary::create([
                                'F1' => PdfIndirectObjectReference::create(3, 0)
                            ]),
                        ]),
                        'Contents' => PdfArray::create([
                            PdfIndirectObjectReference::create(7, 0)
                        ]),
                        'Parent' => PdfIndirectObjectReference::create(8, 0)
                    ])
                ),
                3 => PdfIndirectObject::create(
                    10,
                    0,
                    PdfDictionary::create([
                        'Type' => PdfName::create('Page'),
                        'MediaBox' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfNumeric::create(0),
                            PdfNumeric::create(841.89),
                            PdfNumeric::create(595.28)
                        ]),
                        'Resources' => PdfDictionary::create([
                            'Font' => PdfDictionary::create([
                                'F1' => PdfIndirectObjectReference::create(3, 0)
                            ]),
                        ]),
                        'Contents' => PdfArray::create([
                            PdfIndirectObjectReference::create(11, 0)
                        ]),
                        'Parent' => PdfIndirectObjectReference::create(12, 0)
                    ])
                ),
                4 => PdfIndirectObject::create(
                    14,
                    0,
                    PdfDictionary::create([
                        'Type' => PdfName::create('Page'),
                        'MediaBox' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfNumeric::create(0),
                            PdfNumeric::create(595.28),
                            PdfNumeric::create(841.89)
                        ]),
                        'Resources' => PdfDictionary::create([
                            'Font' => PdfDictionary::create([
                                'F1' => PdfIndirectObjectReference::create(3, 0)
                            ]),
                        ]),
                        'Contents' => PdfArray::create([
                            PdfIndirectObjectReference::create(15, 0)
                        ]),
                        'Parent' => PdfIndirectObjectReference::create(2, 0)
                    ])
                ),
                9 => PdfIndirectObject::create(
                    30,
                    0,
                    PdfDictionary::create([
                        'Type' => PdfName::create('Page'),
                        'MediaBox' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfNumeric::create(0),
                            PdfNumeric::create(841.89),
                            PdfNumeric::create(595.28)
                        ]),
                        'Resources' => PdfDictionary::create([
                            'Font' => PdfDictionary::create([
                                'F1' => PdfIndirectObjectReference::create(3, 0)
                            ]),
                        ]),
                        'Contents' => PdfArray::create([
                            PdfIndirectObjectReference::create(31, 0)
                        ]),
                        'Parent' => PdfIndirectObjectReference::create(32, 0)
                    ])
                ),
                10 => PdfIndirectObject::create(
                    34,
                    0,
                    PdfDictionary::create([
                        'Type' => PdfName::create('Page'),
                        'MediaBox' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfNumeric::create(0),
                            PdfNumeric::create(595.28),
                            PdfNumeric::create(841.89)
                        ]),
                        'Resources' => PdfDictionary::create([
                            'Font' => PdfDictionary::create([
                                'F1' => PdfIndirectObjectReference::create(3, 0)
                            ]),
                        ]),
                        'Contents' => PdfArray::create([
                            PdfIndirectObjectReference::create(35, 0)
                        ]),
                        'Parent' => PdfIndirectObjectReference::create(2, 0)
                    ])
                )
            ]
        ];

        $data[] = [
            $path . '/PageTree2.pdf',
            [
                1 => PdfIndirectObject::create(
                    1,
                    0,
                    PdfDictionary::create([
                        'Group' => PdfDictionary::create([
                            'S' => PdfName::create('Transparency'),
                            'CS' => PdfName::create('DeviceRGB'),
                            'I' => PdfBoolean::create(true)
                        ]),
                        'Parent' => PdfIndirectObjectReference::create(35, 0),
                        'Type' => PdfName::create('Page'),
                        'Contents' => PdfIndirectObjectReference::create(2, 0),
                        'Resources' => PdfIndirectObjectReference::create(3, 0),
                        'MediaBox' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfNumeric::create(0),
                            PdfNumeric::create(595),
                            PdfNumeric::create(842)
                        ]),
                    ])
                ),
                11 => PdfIndirectObject::create(
                    54,
                    0,
                    PdfDictionary::create([
                        'Group' => PdfDictionary::create([
                            'S' => PdfName::create('Transparency'),
                            'CS' => PdfName::create('DeviceRGB'),
                            'I' => PdfBoolean::create(true)
                        ]),
                        'Parent' => PdfIndirectObjectReference::create(56, 0),
                        'Type' => PdfName::create('Page'),
                        'Contents' => PdfIndirectObjectReference::create(55, 0),
                        'Resources' => PdfIndirectObjectReference::create(3, 0),
                        'MediaBox' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfNumeric::create(0),
                            PdfNumeric::create(595),
                            PdfNumeric::create(842)
                        ]),
                    ])
                )
            ]
        ];

        $data[] = [
            $path . '/10000.pdf',
            [
                1 => PdfIndirectObject::create(
                    2,
                    0,
                    PdfDictionary::create([
                        'Type' => PdfName::create('Page'),
                        'MediaBox' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfNumeric::create(0),
                            PdfNumeric::create(595.28),
                            PdfNumeric::create(841.89)
                        ]),
                        'Resources' => PdfDictionary::create([
                            'Font' => PdfDictionary::create([
                                'F1' => PdfIndirectObjectReference::create(1, 0)
                            ])
                        ]),
                        'Parent' => PdfIndirectObjectReference::create(4, 0),
                        'Contents' => PdfArray::create([
                            PdfIndirectObjectReference::create(5, 0)
                        ]),

                    ])
                )
            ],
            10000 => PdfIndirectObject::create(
                20000,
                0,
                PdfDictionary::create([
                    'Type' => PdfName::create('Page'),
                    'MediaBox' => PdfArray::create([
                        PdfNumeric::create(0),
                        PdfNumeric::create(0),
                        PdfNumeric::create(595.28),
                        PdfNumeric::create(841.89)
                    ]),
                    'Resources' => PdfDictionary::create([
                        'Font' => PdfDictionary::create([
                            'F1' => PdfIndirectObjectReference::create(1, 0)
                        ])
                    ]),
                    'Parent' => PdfIndirectObjectReference::create(4, 0),
                    'Contents' => PdfArray::create([
                        PdfIndirectObjectReference::create(20001, 0)
                    ]),

                ])
            )
        ];

        $data[] = [
            $path . '/1000.pdf',
            [
                1 => PdfIndirectObject::create(
                    2,
                    0,
                    PdfDictionary::create([
                        'Type' => PdfName::create('Page'),
                        'MediaBox' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfNumeric::create(0),
                            PdfNumeric::create(595.28),
                            PdfNumeric::create(841.89)
                        ]),
                        'Resources' => PdfDictionary::create([
                            'Font' => PdfDictionary::create([
                                'F1' => PdfIndirectObjectReference::create(1, 0)
                            ])
                        ]),
                        'Parent' => PdfIndirectObjectReference::create(4, 0),
                        'Contents' => PdfArray::create([
                            PdfIndirectObjectReference::create(5, 0)
                        ]),

                    ])
                ),
                100 => PdfIndirectObject::create(
                    202,
                    0,
                    PdfDictionary::create([
                        'Type' => PdfName::create('Page'),
                        'MediaBox' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfNumeric::create(0),
                            PdfNumeric::create(595.28),
                            PdfNumeric::create(841.89)
                        ]),
                        'Resources' => PdfDictionary::create([
                            'Font' => PdfDictionary::create([
                                'F1' => PdfIndirectObjectReference::create(1, 0)
                            ])
                        ]),
                        'Parent' => PdfIndirectObjectReference::create(4, 0),
                        'Contents' => PdfArray::create([
                            PdfIndirectObjectReference::create(203, 0)
                        ]),

                    ])
                ),
                1000 => PdfIndirectObject::create(
                    2002,
                    0,
                    PdfDictionary::create([
                        'Type' => PdfName::create('Page'),
                        'MediaBox' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfNumeric::create(0),
                            PdfNumeric::create(595.28),
                            PdfNumeric::create(841.89)
                        ]),
                        'Resources' => PdfDictionary::create([
                            'Font' => PdfDictionary::create([
                                'F1' => PdfIndirectObjectReference::create(1, 0)
                            ])
                        ]),
                        'Parent' => PdfIndirectObjectReference::create(4, 0),
                        'Contents' => PdfArray::create([
                            PdfIndirectObjectReference::create(2003, 0)
                        ]),

                    ])
                )
            ]
        ];

        $data[] = [
            $path . '/10000_with-tree.pdf',
            [
                1 => PdfIndirectObject::create(
                    31115,
                    0,
                    PdfDictionary::create([
                        'ArtBox' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfNumeric::create(0.169),
                            PdfNumeric::create(595.02),
                            PdfNumeric::create(841.691)
                        ]),
                        'BleedBox' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfNumeric::create(0.309),
                            PdfNumeric::create(595.02),
                            PdfNumeric::create(841.831)
                        ]),
                        'Contents' => PdfIndirectObjectReference::create(31121, 0),
                        'CropBox' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfNumeric::create(0),
                            PdfNumeric::create(595.22),
                            PdfNumeric::create(842)
                        ]),
                        'MediaBox' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfNumeric::create(0),
                            PdfNumeric::create(595.22),
                            PdfNumeric::create(842)
                        ]),
                        'Parent' => PdfIndirectObjectReference::create(30003, 0),
                        'Resources' => PdfIndirectObjectReference::create(31116, 0),
                        'Rotate' => PdfNumeric::create(0),
                        'TrimBox' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfNumeric::create(0.309),
                            PdfNumeric::create(595.02),
                            PdfNumeric::create(841.831)
                        ]),
                        'Type' => PdfName::create('Page')
                    ])
                ),
                10000 => PdfIndirectObject::create(
                    29995,
                    0,
                    PdfDictionary::create([
                        'ArtBox' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfNumeric::create(0.169),
                            PdfNumeric::create(595.02),
                            PdfNumeric::create(841.691)
                        ]),
                        'BleedBox' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfNumeric::create(0.309),
                            PdfNumeric::create(595.02),
                            PdfNumeric::create(841.831)
                        ]),
                        'Contents' => PdfIndirectObjectReference::create(29997, 0),
                        'CropBox' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfNumeric::create(0),
                            PdfNumeric::create(595.22),
                            PdfNumeric::create(842)
                        ]),
                        'MediaBox' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfNumeric::create(0),
                            PdfNumeric::create(595.22),
                            PdfNumeric::create(842)
                        ]),
                        'Parent' => PdfIndirectObjectReference::create(31110, 0),
                        'Resources' => PdfIndirectObjectReference::create(29996, 0),
                        'Rotate' => PdfNumeric::create(0),
                        'TrimBox' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfNumeric::create(0.309),
                            PdfNumeric::create(595.02),
                            PdfNumeric::create(841.831)
                        ]),
                        'Type' => PdfName::create('Page')
                    ])
                ),
                9999 => PdfIndirectObject::create(
                    29992,
                    0,
                    PdfDictionary::create([
                        'ArtBox' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfNumeric::create(0.169),
                            PdfNumeric::create(595.02),
                            PdfNumeric::create(841.691)
                        ]),
                        'BleedBox' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfNumeric::create(0.309),
                            PdfNumeric::create(595.02),
                            PdfNumeric::create(841.831)
                        ]),
                        'Contents' => PdfIndirectObjectReference::create(29994, 0),
                        'CropBox' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfNumeric::create(0),
                            PdfNumeric::create(595.22),
                            PdfNumeric::create(842)
                        ]),
                        'MediaBox' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfNumeric::create(0),
                            PdfNumeric::create(595.22),
                            PdfNumeric::create(842)
                        ]),
                        'Parent' => PdfIndirectObjectReference::create(31110, 0),
                        'Resources' => PdfIndirectObjectReference::create(29993, 0),
                        'Rotate' => PdfNumeric::create(0),
                        'TrimBox' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfNumeric::create(0.309),
                            PdfNumeric::create(595.02),
                            PdfNumeric::create(841.831)
                        ]),
                        'Type' => PdfName::create('Page')
                    ])
                ),
                9991 => PdfIndirectObject::create(
                    29968,
                    0,
                    PdfDictionary::create([
                        'ArtBox' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfNumeric::create(0.169),
                            PdfNumeric::create(595.02),
                            PdfNumeric::create(841.691)
                        ]),
                        'BleedBox' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfNumeric::create(0.309),
                            PdfNumeric::create(595.02),
                            PdfNumeric::create(841.831)
                        ]),
                        'Contents' => PdfIndirectObjectReference::create(29970, 0),
                        'CropBox' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfNumeric::create(0),
                            PdfNumeric::create(595.22),
                            PdfNumeric::create(842)
                        ]),
                        'MediaBox' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfNumeric::create(0),
                            PdfNumeric::create(595.22),
                            PdfNumeric::create(842)
                        ]),
                        'Parent' => PdfIndirectObjectReference::create(31110, 0),
                        'Resources' => PdfIndirectObjectReference::create(29969, 0),
                        'Rotate' => PdfNumeric::create(0),
                        'TrimBox' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfNumeric::create(0.309),
                            PdfNumeric::create(595.02),
                            PdfNumeric::create(841.831)
                        ]),
                        'Type' => PdfName::create('Page')
                    ])
                ),
                9990 => PdfIndirectObject::create(
                    29965,
                    0,
                    PdfDictionary::create([
                        'ArtBox' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfNumeric::create(0.169),
                            PdfNumeric::create(595.02),
                            PdfNumeric::create(841.691)
                        ]),
                        'BleedBox' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfNumeric::create(0.309),
                            PdfNumeric::create(595.02),
                            PdfNumeric::create(841.831)
                        ]),
                        'Contents' => PdfIndirectObjectReference::create(29967, 0),
                        'CropBox' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfNumeric::create(0),
                            PdfNumeric::create(595.22),
                            PdfNumeric::create(842)
                        ]),
                        'MediaBox' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfNumeric::create(0),
                            PdfNumeric::create(595.22),
                            PdfNumeric::create(842)
                        ]),
                        'Parent' => PdfIndirectObjectReference::create(31109, 0),
                        'Resources' => PdfIndirectObjectReference::create(29966, 0),
                        'Rotate' => PdfNumeric::create(0),
                        'TrimBox' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfNumeric::create(0.309),
                            PdfNumeric::create(595.02),
                            PdfNumeric::create(841.831)
                        ]),
                        'Type' => PdfName::create('Page')
                    ])
                )
            ]
        ];

        $data[] = [
            $path . '/PageTreeWithEmptyKids.pdf',
            [
                1 => PdfIndirectObject::create(
                    4,
                    0,
                    PdfDictionary::create([
                        'Type' => PdfName::create('Page'),
                        'MediaBox' => PdfArray::create([
                            PdfNumeric::create(0),
                            PdfNumeric::create(0),
                            PdfNumeric::create(841.89),
                            PdfNumeric::create(595.28)
                        ]),
                        'Resources' => PdfDictionary::create([
                            'Font' => PdfDictionary::create([
                                'F1' => PdfIndirectObjectReference::create(3, 0)
                            ])
                        ]),
                        'Contents' => PdfArray::create([
                            PdfIndirectObjectReference::create(5, 0)
                        ]),
                        'Parent' => PdfIndirectObjectReference::create(2, 0),
                    ])
                )
            ]
        ];

        return $data;
    }

    /**
     * @param $path
     * @param array $expectedResults
     * @dataProvider getPageProvider
     */
    public function testGetPage($path, array $expectedResults)
    {
        $stream = StreamReader::createByFile($path);
        $parser = new PdfParser($stream);

        $pdfReader = new PdfReader($parser);

        foreach ($expectedResults as $pageNumber => $expectedResult) {
            $this->assertEquals($expectedResult, $pdfReader->getPage($pageNumber)->getPageObject());
        }
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetPageWithInvalidArgument()
    {
        $path = __DIR__ . '/../../_files/pdfs/Boombastic-Box.pdf';
        $stream = StreamReader::createByFile($path);
        $parser = new PdfParser($stream);

        $pdfReader = new PdfReader($parser);
        $pdfReader->getPage('nothing numeric');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetPageWithInvalidArgument2()
    {
        $path = __DIR__ . '/../../_files/pdfs/Boombastic-Box.pdf';
        $stream = StreamReader::createByFile($path);
        $parser = new PdfParser($stream);

        $pdfReader = new PdfReader($parser);
        $pdfReader->getPage(100000);
    }

    /**
     * @expectedException \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException
     * @expectedExceptionCode \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException::ENCRYPTED
     */
    public function testHandlingOfEncryptedPdf()
    {
        $path = __DIR__ . '/../../_files/pdfs/encrypted/ex37.pdf';
        $stream = StreamReader::createByFile($path);
        $parser = new PdfParser($stream);

        $pdfReader = new PdfReader($parser);
        $pdfReader->getPageCount();
    }

    /**
     * @expectedException \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException
     * @expectedExceptionCode \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException::ENCRYPTED
     */
    public function testHandlingOfEncryptedPdfWithCompressedXref()
    {
        $path = __DIR__ . '/../../_files/pdfs/encrypted/AES256-R6-u=user-o=owner.pdf';
        $stream = StreamReader::createByFile($path);
        $parser = new PdfParser($stream);

        $pdfReader = new PdfReader($parser);
        $pdfReader->getPageCount();
    }
}