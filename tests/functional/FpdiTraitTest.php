<?php

namespace setasign\Fpdi\functional;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\Fpdi;

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

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetTemplateSizeWithZeroWidth()
    {
        $pdf = new Fpdi();
        $pdf->setSourceFile(__DIR__ . '/../_files/pdfs/boxes/All2.pdf');
        $pdf->getTemplateSize($pdf->importPage(1), 0);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetTemplateSizeWithZeroHeight()
    {
        $pdf = new Fpdi();
        $pdf->setSourceFile(__DIR__ . '/../_files/pdfs/boxes/All2.pdf');
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

    /**
     * @expectedException InvalidArgumentException
     */
    public function testImportPageWithInvalidBoxParameter()
    {
        $pdf = new Fpdi();
        $pdf->setSourceFile(__DIR__ . '/../_files/pdfs/boxes/All2.pdf');
        $pdf->importPage(1, 'CropsBox');
    }
}