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

    public function getInstance()
    {
        return new FpdfTpl('P', 'pt');
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
                    '_method' => 'colorHandlingA',
                    'tmpPath' => 'colorHandlingA',
                ],
                0.1,
                72 // dpi
            ],
            [
                [
                    '_method' => 'colorHandlingB',
                    'tmpPath' => 'colorHandlingB',
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
            ],
            [
                [
                    '_method' => 'underlineHandling',
                    'tmpPath' => 'underlineHandling'
                ],
                0.1,
                72
            ]

        ];
    }

    public function templateInTemplate($inputData, $outputFile)
    {
        $pdf = $this->getInstance();
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

        $pdf->Output($outputFile, 'F');
    }

    /**
     * No font was defined in the pages content scope. So it is needed to re-define it.
     *
     * @param $inputData
     * @param $outputFile
     */
    public function fontHandlingA($inputData, $outputFile)
    {
        $pdf = $this->getInstance();
        $pdf->AddPage();

        $tplIdx = $pdf->beginTemplate(100, 12);
            $pdf->SetFont('Helvetica', '', 12);
            $pdf->SetXY(0, 0);
            $pdf->Cell(100, 12, 'My Test Template', 1);
        $pdf->endTemplate();

        $pdf->useTemplate($tplIdx, 10, 10);

        $pdf->SetFont('Helvetica', '', 12); // This line is required!
        $pdf->Text(200, 200, 'Text');

        $pdf->Output($outputFile, 'F');
    }

    /**
     * Font is defined in page scope and will be re-used in the template
     *
     * @param $inputData
     * @param $outputFile
     */
    public function fontHandlingB($inputData, $outputFile)
    {
        $pdf = $this->getInstance();
        $pdf->AddPage();
        $pdf->SetFont('Helvetica', '', 12);

        $tplIdx = $pdf->beginTemplate(100, 12);
            $pdf->SetXY(0, 0);
            $pdf->Cell(100, 12, 'My Test Template', 1);
        $pdf->endTemplate();

        $pdf->useTemplate($tplIdx, 10, 10);

        $pdf->Text(200, 200, 'Text');

        $pdf->Output($outputFile, 'F');
    }

    public function colorHandlingA($inputData, $outputFile)
    {
        $pdf = $this->getInstance();
        $pdf->AddPage();
        $pdf->SetDrawColor(255, 0, 0);
        $pdf->SetFillColor(0, 255, 0);
        $pdf->SetTextColor(0, 0, 255);
        $pdf->SetFont('Helvetica', '', 12);

        $tplIdx = $pdf->beginTemplate(100, 12);
        $pdf->SetDrawColor(255, 0, 255);
        $pdf->SetFillColor(255, 255, 0);
        $pdf->SetTextColor(255, 0, 0);
        $pdf->SetXY(0, 0);
        $pdf->Cell(100, 12, 'My Test Template', 1, 0, '', 'FD');
        $pdf->endTemplate();

        $tplIdx2 = $pdf->beginTemplate(120, 12);
        $pdf->SetDrawColor(255, 255, 0);
        $pdf->SetFillColor(255, 0, 255);
        $pdf->SetTextColor(0, 255, 255);
        $pdf->SetXY(0, 0);
        $pdf->Cell(120, 12, 'My Test Template 2', 1, 0, '', 'FD');
        $pdf->endTemplate();

        $pdf->SetXY(10, 10);
        $pdf->Cell(100, 12, 'Another test', 1, 0, '', 'FD');

        $pdf->useTemplate($tplIdx, 10, 30, 400);
        $pdf->useTemplate($tplIdx2, 10, 80, 400);

        $pdf->SetXY(10, 130);
        $pdf->Cell(100, 12, 'Another test', 1, 0, '', 'FD');

        $pdf->SetXY(10, 150);
        $pdf->SetTextColor(255);
        $pdf->Cell(100, 12, 'Another test', 1, 0, '', 'FD');

        $tplIdx3 = $pdf->beginTemplate(120, 12);
        $pdf->SetXY(0, 0);
        $pdf->Cell(120, 12, 'My Test Template 3', 1, 0, '', 'FD');
        $pdf->SetTextColor(0, 255, 0);
        $pdf->endTemplate();
        $pdf->useTemplate($tplIdx3, 10, 180, 400);

        $pdf->SetXY(10, 240);
        $pdf->Cell(100, 12, 'Another test', 1, 0, '', 'FD');

        $pdf->Output($outputFile, 'F');
    }

    public function colorHandlingB($inputData, $outputFile)
    {
        $pdf = $this->getInstance();
        $pdf->AddPage();

        $tplIdx = $pdf->beginTemplate(100, 12);
        $pdf->SetTextColor(150);
        $pdf->SetDrawColor(200);
        $pdf->SetFont('Helvetica', '', 12);
        $pdf->SetXY(0, 0);
        $pdf->Cell(100, 12, 'My Test Template', 1, 0, '', 'FD');
        $pdf->endTemplate();

        $pdf->useTemplate($tplIdx, 10, 10);

        $pdf->SetFont('Helvetica', '', 12);
        $pdf->SetFillColor(255, 0, 0);
        $pdf->SetXY(10, 40);
        $pdf->Cell(100, 12, 'Another test', 1, 0, '', 'FD');

        $pdf->Output($outputFile, 'F');
    }

    public function adjustPageSize($inputData, $outputFile)
    {
        $pdf = $this->getInstance();
        $tplId = $pdf->beginTemplate(200, 100);
        $pdf->SetDrawColor(255, 0, 0);
        $pdf->SetFillColor(0, 255, 0);
        $pdf->SetLineWidth(10);
        $pdf->Rect(0, 0, 200, 100, 'DF');
        $pdf->endTemplate();

        $pdf->AddPage();
        $pdf->useTemplate($tplId, ['adjustPageSize' => true]);

        $pdf->Output($outputFile, 'F');
    }

    public function underlineHandling($inputData, $outputFile)
    {
        $pdf = $this->getInstance();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'U', 12);
        $pdf->Cell(0, 14, 'Underline Text.', 0, 1);

        $x = $pdf->GetX();
        $y = $pdf->GetY();

        $tplId = $pdf->beginTemplate(200, 20);
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetXY(0, 0);
        $pdf->Cell(0, 14, 'No underline in template');
        $pdf->endTemplate();

        $pdf->useTemplate($tplId, $x, $y);

        $pdf->Ln();
        $pdf->Cell(0, 14, 'Underline text again.', 0, 1);

        $pdf->Output($outputFile, 'F');
    }
}
