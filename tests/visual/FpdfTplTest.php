<?php

namespace setasign\Fpdi\visual;

use setasign\Fpdi\FpdfTpl;

class FpdfTplTest extends VisualTestCase
{
    /**
     * Should return __FILE__
     *
     * @return string
     */
    public function getClassFile()
    {
        return __FILE__;
    }

    public function createProvider()
    {
        return [
            [
                [
                    '_method' => 'templateInTemplate',
                    'tmpPath' => 'templateInTemplate',
                ],
                0.1,
                72 // dpi
            ],
            [
                [
                    '_method' => 'fontHandlingA',
                    'tmpPath' => 'fontHandlingA',
                ],
                0.1,
                72 // dpi
            ],
            [
                [
                    '_method' => 'fontHandlingB',
                    'tmpPath' => 'fontHandlingB',
                ],
                0.1,
                72 // dpi
            ],
            [
                [
                    '_method' => 'colorHandling',
                    'tmpPath' => 'colorHandling',
                ],
                0.1,
                72 // dpi
            ],
            [
                [
                    '_method' => 'adjustPageSize',
                    'tmpPath' => 'adjustPageSize'
                ],
                0.1,
                72
            ]

        ];
    }

    public function templateInTemplate($inputData, $outputFile)
    {
        $pdf = new FpdfTpl('P', 'pt');
        $pdf->AddPage();

        $tplIdx = $pdf->beginTemplate(100, 12);
            $pdf->SetFont('Helvetica', '', 12);
            $pdf->SetXY(0, 0);
            $pdf->Cell(100, 12, 'My Test Template', 1);
        $pdf->endTemplate();

        $tplIdx2 = $pdf->beginTemplate(50, 6);
            $pdf->useTemplate($tplIdx, 0, 0, 50);
        $pdf->endTemplate();

        $tplIdx3 = $pdf->beginTemplate();
            $size = $pdf->useTemplate($tplIdx);
            $pdf->useTemplate($tplIdx2, 0, $size['height']);
        $pdf->endTemplate();

        $pdf->useTemplate($tplIdx3, 10, 10);

        $pdf->Output('F', $outputFile);
    }

    /**
     * No font was defined in the pages content scope. So it is needed to re-define it.
     *
     * @param $inputData
     * @param $outputFile
     */
    public function fontHandlingA($inputData, $outputFile)
    {
        $pdf = new FpdfTpl('P', 'pt');
        $pdf->AddPage();

        $tplIdx = $pdf->beginTemplate(100, 12);
            $pdf->SetFont('Helvetica', '', 12);
            $pdf->SetXY(0, 0);
            $pdf->Cell(100, 12, 'My Test Template', 1);
        $pdf->endTemplate();

        $pdf->useTemplate($tplIdx, 10, 10);

        $pdf->SetFont('Helvetica', '', 12); // This line is required!
        $pdf->Text(200, 200, 'Text');

        $pdf->Output('F', $outputFile);
    }

    /**
     * Font is defined in page scope and will be re-used in the template
     *
     * @param $inputData
     * @param $outputFile
     */
    public function fontHandlingB($inputData, $outputFile)
    {
        $pdf = new FpdfTpl('P', 'pt');
        $pdf->AddPage();
        $pdf->SetFont('Helvetica', '', 12);

        $tplIdx = $pdf->beginTemplate(100, 12);
            $pdf->SetXY(0, 0);
            $pdf->Cell(100, 12, 'My Test Template', 1);
        $pdf->endTemplate();

        $pdf->useTemplate($tplIdx, 10, 10);

        $pdf->Text(200, 200, 'Text');

        $pdf->Output('F', $outputFile);
    }

    public function colorHandling($inputData, $outputFile)
    {
        $pdf = new FpdfTpl('P', 'pt');
        $pdf->AddPage();
        $pdf->SetDrawColor(255, 0, 0);
        $pdf->SetFillColor(0, 255, 0);
        $pdf->SetTextColor(0, 0, 255);
        $pdf->SetFont('Helvetica', '', 12);

        $tplIdx = $pdf->beginTemplate(100, 12);
        $pdf->SetXY(0, 0);
        $pdf->Cell(100, 12, 'My Test Template', 1, 0, '', 'FD');
        $pdf->endTemplate();

        $pdf->useTemplate($tplIdx, 10, 10, 400);

        $pdf->Output('F', $outputFile);
    }

    public function adjustPageSize($inputData, $outputFile)
    {
        $pdf = new FpdfTpl('P', 'pt');
        $tplId = $pdf->beginTemplate(200, 100);
        $pdf->SetDrawColor(255, 0, 0);
        $pdf->SetFillColor(0, 255, 0);
        $pdf->SetLineWidth(10);
        $pdf->Rect(0, 0, 200, 100, 'DF');
        $pdf->endTemplate();

        $pdf->AddPage();
        $pdf->useTemplate($tplId, ['adjustPageSize' => true]);

        $pdf->Output('F', $outputFile);
    }
}
