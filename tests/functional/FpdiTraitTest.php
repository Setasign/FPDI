<?php

namespace setasign\Fpdi\functional;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\Type\PdfArray;
use setasign\Fpdi\PdfParser\Type\PdfBoolean;
use setasign\Fpdi\PdfParser\Type\PdfDictionary;
use setasign\Fpdi\PdfParser\Type\PdfHexString;
use setasign\Fpdi\PdfParser\Type\PdfIndirectObject;
use setasign\Fpdi\PdfParser\Type\PdfIndirectObjectReference;
use setasign\Fpdi\PdfParser\Type\PdfName;
use setasign\Fpdi\PdfParser\Type\PdfNull;
use setasign\Fpdi\PdfParser\Type\PdfNumeric;
use setasign\Fpdi\PdfParser\Type\PdfStream;
use setasign\Fpdi\PdfParser\Type\PdfString;
use setasign\Fpdi\PdfParser\Type\PdfToken;
use setasign\Fpdi\PdfParser\Type\PdfType;

class FpdiTraitTest extends TestCase
{
    public function testGetTemplateSizeWithPt()
    {
        $pdf = new Fpdi('P', 'pt');
        $pdf->setSourceFile(__DIR__ . '/../_files/pdfs/boxes/All2.pdf');
        $size = $pdf->getTemplateSize($pdf->importPage(1));

        $this->assertEquals([
            'width' => 420,
            'height' => 920,
            0 => 420,
            1 => 920,
            'orientation' => 'P'
        ], $size);
    }

    public function testGetTemplateSizeWithMM()
    {
        $pdf = new Fpdi('P', 'mm');
        $pdf->setSourceFile(__DIR__ . '/../_files/pdfs/boxes/All2.pdf');
        $size = $pdf->getTemplateSize($pdf->importPage(1));

        $this->assertEquals([
            'width' => 148.16666666666666,
            'height' => 324.55555555555554,
            0 => 148.16666666666666,
            1 => 324.55555555555554,
            'orientation' => 'P'
        ], $size);
    }

    public function testGetTemplateSizeResizedByWidth()
    {
        $pdf = new Fpdi('P', 'pt');
        $pdf->setSourceFile(__DIR__ . '/../_files/pdfs/boxes/All2.pdf');
        $size = $pdf->getTemplateSize($pdf->importPage(1), 42);

        $this->assertEquals([
            'width' => 42,
            'height' => 92,
            0 => 42,
            1 => 92,
            'orientation' => 'P'
        ], $size);
    }

    public function testGetTemplateSizeResizedByHeight()
    {
        $pdf = new Fpdi('P', 'pt');
        $pdf->setSourceFile(__DIR__ . '/../_files/pdfs/boxes/All2.pdf');
        $size = $pdf->getTemplateSize($pdf->importPage(1), null, 92);

        $this->assertEquals([
            'width' => 42,
            'height' => 92,
            0 => 42,
            1 => 92,
            'orientation' => 'P'
        ], $size);
    }

    public function testGetTemplateSizeWithZeroWidth()
    {
        $pdf = new Fpdi();
        $pdf->setSourceFile(__DIR__ . '/../_files/pdfs/boxes/All2.pdf');
        $this->expectException(\InvalidArgumentException::class);
        $pdf->getTemplateSize($pdf->importPage(1), 0);
    }

    public function testGetTemplateSizeWithZeroHeight()
    {
        $pdf = new Fpdi();
        $pdf->setSourceFile(__DIR__ . '/../_files/pdfs/boxes/All2.pdf');
        $this->expectException(\InvalidArgumentException::class);
        $pdf->getTemplateSize($pdf->importPage(1), null, 0);
    }

    public function setSourceFileProvider()
    {
        $data = [];
        $path = __DIR__ . '/../_files/pdfs';

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
            $path . '/specials/page-trees/PageTree.pdf',
            10
        ];

        $data[] = [
            $path . '/specials/page-trees/PageTree2.pdf',
            13
        ];

        return $data;
    }

    /**
     * @param $path
     * @param $expectedCount
     * @dataProvider setSourceFileProvider
     */
    public function testSetSourceFile($path, $expectedCount)
    {
        $pdf = new Fpdi();
        $this->assertSame($expectedCount, $pdf->setSourceFile($path));
    }

    public function testImportPageWithSameMeaningBoxParameters()
    {
        $pdf = new Fpdi();
        $pdf->setSourceFile(__DIR__ . '/../_files/pdfs/boxes/All2.pdf');
        $idA = $pdf->importPage(1, '/CropBox');
        $idB = $pdf->importPage(1, 'CropBox');

        $this->assertEquals($idA, $idB);
    }

    public function testImportPageWithInvalidBoxParameter()
    {
        $pdf = new Fpdi();
        $pdf->setSourceFile(__DIR__ . '/../_files/pdfs/boxes/All2.pdf');
        $this->expectException(\InvalidArgumentException::class);
        $pdf->importPage(1, 'CropsBox');
    }

    public function faultyStructuresProvider()
    {
        return [
            [__DIR__ . '/../_files/pdfs/specials/NoContentsEntry.pdf'],
            [__DIR__ . '/../_files/pdfs/specials/ContentsArrayWithNoStream.pdf'],
            [__DIR__ . '/../_files/pdfs/specials/ContentsArrayWithReferenceToNotExistingObject.pdf'],
            [__DIR__ . '/../_files/pdfs/specials/ContentsWithReferenceToNotExistingObject.pdf'],
        ];
    }

    /**
     * @param $path
     * @param $pageNo
     * @dataProvider faultyStructuresProvider
     */
    public function testFaultyStructures($path, $pageNo = 1)
    {
        $pdf = new Fpdi();
        $pdf->setSourceFile($path);

        $id = $pdf->importPage($pageNo);
        $this->assertTrue(isset($id));
    }

    public function writePdfTypeProvider()
    {
        return [
            [
                PdfBoolean::create(true),
                'true '
            ],
            [
                PdfHexString::create('48656c6c6f20576f726c64'),
                '<48656c6c6f20576f726c64>'
            ],
            [
                PdfString::create('Hello FPDI'),
                '(Hello FPDI)'
            ],
            [
                PdfName::create('FPDI'),
                '/FPDI '
            ],
            [
                new PdfNull(),
                'null '
            ],
            [
                PdfNumeric::create(1.234566),
                '1.23457 '
            ],
            [
                PdfNumeric::create('1.00000'),
                '1 '
            ],
            [
                PdfNumeric::create('01234'),
                '1234 '
            ],
            [
                PdfIndirectObjectReference::create(123, 0),
                '1 0 R ' // this is correct because the imported objects will be remapped to new object ids.
            ],
            [
                PdfToken::create('AnyToken'),
                "AnyToken\n"
            ],
            [
                PdfArray::create([
                    PdfNumeric::create(1),
                    PdfString::create('Hey')
                ]),
                "[1 (Hey)]\n"
            ],
            [
                PdfDictionary::create([
                    'A' => PdfName::create('Ok'),
                    'B' => PdfArray::create([

                    ]),
                    'C' => PdfString::create('C')
                ]),
                "<</A /Ok /B []\n" .
                "/C (C)>>\n"
            ],
            [
                PdfStream::create(PdfDictionary::create(), 'Testen'),
                "<<>>\n" .
                "stream\n" .
                "Testen\n" .
                "endstream\n"
            ],
            [
                PdfIndirectObject::create(1, 0, PdfArray::create()),
                "1 0 obj\n" .
                "[]\n" .
                "endobj\n"
            ],
            [
                PdfIndirectObject::create(1, 0, PdfBoolean::create(false)),
                "1 0 obj\n" .
                "false \n" .
                "endobj\n"
            ],
            [
                PdfIndirectObject::create(1, 0, PdfDictionary::create()),
                "1 0 obj\n" .
                "<<>>\n" .
                "endobj\n"
            ],
            [
                PdfIndirectObject::create(1, 0, PdfHexString::create('')),
                "1 0 obj\n" .
                "<>\n" .
                "endobj\n"
            ],
            [
                PdfIndirectObject::create(1, 0, PdfIndirectObjectReference::create(2, 0)),
                "1 0 obj\n" .
                "2 0 R \n" .
                "endobj\n"
            ],
            [
                PdfIndirectObject::create(1, 0, PdfName::create('FPDI')),
                "1 0 obj\n" .
                "/FPDI \n" .
                "endobj\n"
            ],
            [
                PdfIndirectObject::create(1, 0, new PdfNull()),
                "1 0 obj\n" .
                "null \n" .
                "endobj\n"
            ],
            [
                PdfIndirectObject::create(1, 0, PdfNumeric::create(123)),
                "1 0 obj\n" .
                "123 \n" .
                "endobj\n"
            ],
            [
                PdfIndirectObject::create(1, 0, PdfStream::create(PdfDictionary::create(), 'Test')),
                "1 0 obj\n" .
                "<<>>\n" .
                "stream\n" .
                "Test\n" .
                "endstream\n" .
                "endobj\n"
            ],
            [
                PdfIndirectObject::create(1, 0, PdfString::create('FPDI')),
                "1 0 obj\n" .
                "(FPDI)\n" .
                "endobj\n"
            ],
            [
                PdfIndirectObject::create(1, 0, PdfToken::create('AnyToken')),
                "1 0 obj\n" .
                "AnyToken\n" .
                "endobj\n"
            ],
        ];
    }

    /**
     * @param $value
     * @param $expected
     * @return void
     * @dataProvider writePdfTypeProvider
     */
    public function testWritePdfType($value, $expected)
    {
        $instance = new FpdiTraitTestClass();
        $result = $instance->simulateWritePdfType($value);
        $this->assertSame($expected, $result);
    }

    public function testWritingOfIndirectObjectsAndReferences()
    {
        $instance = new FpdiTraitTestClass();
        $result = $instance->simulateWritePdfType(PdfIndirectObjectReference::create(123, 0));
        $this->assertSame('1 0 R ', $result);

        $result = $instance->simulateWritePdfType(PdfIndirectObjectReference::create(124, 0));
        $this->assertSame('2 0 R ', $result);

        $result = $instance->simulateWritePdfType(PdfIndirectObject::create(123, 0, new PdfNull()));
        $this->assertSame(
            "1 0 obj\n" .
            "null \n" .
            "endobj\n",
            $result
        );

        $result = $instance->simulateWritePdfType(PdfIndirectObject::create(124, 0, new PdfNull()));
        $this->assertSame(
            "2 0 obj\n" .
            "null \n" .
            "endobj\n",
            $result
        );

    }
}