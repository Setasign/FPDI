<?php

namespace setasign\Fpdi\visual\Alpha;

use setasign\Fpdi\visual\VisualTestCase;

class AlphaTest extends VisualTestCase
{
    public function createProvider()
    {
        $path = __DIR__ . '/../..';
        return [
            0 => [
                [
                    'file' => $path . '/_files/pdfs/transparency/ex74.pdf',
                    'tmpPath' => '0'
                ],
                0.5,
                36
            ]
        ];
    }

    /**
     * Simply re-use a PDF which already uses transparency and apply additional transparency.
     *
     * @param string|array $inputData
     * @param string $outputFile
     */
    public function createPDF($inputData, $outputFile)
    {
        $pdf = new AlphaPdf();

        $pdf->AddPage();

        $pageCount = $pdf->setSourceFile($inputData['file']);
        $tplIdA = $pdf->importPage(1, 'CropBox', true);
        $tplIdB = $pdf->importPage(1, 'CropBox', false);

        $pdf->SetAlpha(.1);

        $pdf->useTemplate($tplIdA, 40, 50, 100);
        $pdf->useTemplate($tplIdB, 160, 50, 100);

        $pdf->Output($outputFile, 'F');
    }

    /**
     * Should return __FILE__
     *
     * @return string
     */
    public function getClassFile()
    {
        return __FILE__;
    }
}
