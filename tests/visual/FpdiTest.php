<?php

namespace setasign\Fpdi\visual;

use setasign\Fpdi\Fpdi;

class FpdiTest extends VisualTestCase
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

    public function getInstance($unit = 'pt')
    {
        return new Fpdi('P', $unit);
    }

    public function createProvider()
    {
        return [
            [
                [
                    '_method' => 'importedPageInTemplatePt1',
                    'tmpPath' => 'importedPageInTemplatePt1',
                ],
                0.1,
                72 // dpi
            ],
            [
                [
                    '_method' => 'importedPageInTemplateMm1',
                    'tmpPath' => 'importedPageInTemplateMm1',
                ],
                0.1,
                72 // dpi
            ],
            [
                [
                    '_method' => 'importedPageInTemplatePt2',
                    'tmpPath' => 'importedPageInTemplatePt2',
                ],
                0.1,
                72 // dpi
            ],
            [
                [
                    '_method' => 'importedPageInTemplateMm2',
                    'tmpPath' => 'importedPageInTemplateMm2',
                ],
                0.1,
                72 // dpi
            ],
        ];
    }

    public function importedPageInTemplatePt1($inputData, $outputFile)
    {
        $pdf = $this->getInstance();

        $pdf->setSourceFile(__DIR__ . '/../_files/pdfs/Boombastic-Box.pdf');
        $pageIdx = $pdf->importPage(1);

        // page looks identically
        $pdf->AddPage();
        $pdf->beginTemplate();
        $size = $pdf->useTemplate($pageIdx);
        $pdf->SetDrawColor(255, 0, 255);
        $pdf->Rect(0, 0, $size['width'], $size['height']);
        $tplIdx = $pdf->endTemplate();
        $pdf->useTemplate($tplIdx);

        // draw template with an offset and different size
        $pdf->AddPage();
        $size = $pdf->useTemplate($tplIdx, 10, 50, 250);
        $pdf->SetDrawColor(0, 255, 0);
        $pdf->Rect(10, 50, $size['width'], $size['height']);

        $pdf->Output('F', $outputFile);
    }

    public function importedPageInTemplateMm1($inputData, $outputFile)
    {
        $pdf = $this->getInstance('mm');

        $pdf->setSourceFile(__DIR__ . '/../_files/pdfs/Boombastic-Box.pdf');
        $pageIdx = $pdf->importPage(1);

        // page looks identically
        $pdf->AddPage();
        $pdf->beginTemplate();
        $size = $pdf->useTemplate($pageIdx);
        $pdf->SetDrawColor(255, 0, 255);
        $pdf->Rect(0, 0, $size['width'], $size['height']);
        $tplIdx = $pdf->endTemplate();
        $pdf->useTemplate($tplIdx);

        // draw template with an offset and different size
        $pdf->AddPage();
        $size = $pdf->useTemplate($tplIdx, 10, 20, 100);
        $pdf->SetDrawColor(0, 255, 0);
        $pdf->Rect(10, 20, $size['width'], $size['height']);

        $pdf->Output('F', $outputFile);
    }

    public function importedPageInTemplatePt2($inputData, $outputFile)
    {
        $pdf = $this->getInstance();

        $pdf->setSourceFile(__DIR__ . '/../_files/pdfs/Boombastic-Box.pdf');
        $pageIdx = $pdf->importPage(1);

        // draw the page onto another templage
        $pdf->AddPage();
        $pdf->beginTemplate(100, 200);
            $pdf->SetDrawColor(255, 0, 0);
            $pdf->Rect(0, 0, 100, 200);
            $size = $pdf->useTemplate($pageIdx, 10, 10, 80, 180);
            $pdf->SetDrawColor(0, 0, 255);
            $pdf->Rect(10, 10, $size['width'], $size['height']);
        $tplIdx = $pdf->endTemplate();
        $pdf->useTemplate($tplIdx, 10, 10);

        // create an additional template and draw the previous template onto this
        $pdf->AddPage();
        $pdf->beginTemplate(150, 200);
            $pdf->SetDrawColor(255, 0, 0);
            $pdf->Rect(0, 0, 150, 200);
            $size = $pdf->useTemplate($tplIdx, 10, 10, null, 180);
            $pdf->SetDrawColor(0, 255, 0);
            $pdf->Rect(10, 10, $size['width'], 180);
        $tplIdx2 = $pdf->endTemplate();
        $pdf->useTemplate($tplIdx2, 30, 100);

        $pdf->Output('F', $outputFile);
    }

    public function importedPageInTemplateMm2($inputData, $outputFile)
    {
        $pdf = $this->getInstance('mm');

        $pdf->setSourceFile(__DIR__ . '/../_files/pdfs/Boombastic-Box.pdf');
        $pageIdx = $pdf->importPage(1);

        // draw the page onto another templage
        $pdf->AddPage();
        $pdf->beginTemplate(100, 200);
            $pdf->SetDrawColor(255, 0, 0);
            $pdf->Rect(0, 0, 100, 200);
            $size = $pdf->useTemplate($pageIdx, 10, 10, 80, 180);
            $pdf->SetDrawColor(0, 0, 255);
            $pdf->Rect(10, 10, $size['width'], $size['height']);
        $tplIdx = $pdf->endTemplate();
        $pdf->useTemplate($tplIdx, 10, 10);

        // create an additional template and draw the previous template onto this
        $pdf->AddPage();
        $pdf->beginTemplate(100, 150);
            $pdf->SetDrawColor(255, 0, 0);
            $pdf->Rect(0, 0, 100, 150);
            $size = $pdf->useTemplate($tplIdx, 10, 10, null, 130);
            $pdf->SetDrawColor(0, 255, 0);
            $pdf->Rect(10, 10, $size['width'], 130);
        $tplIdx2 = $pdf->endTemplate();
        $pdf->useTemplate($tplIdx2, 10, 30);

        $pdf->Output('F', $outputFile);
    }
}