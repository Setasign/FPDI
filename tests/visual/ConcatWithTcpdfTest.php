<?php

namespace setasign\Fpdi\visual;

use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader\PageBoundaries;
use setasign\Fpdi\TcpdfFpdi;

class ConcatWithTcpdfTest extends ConcatTest
{
    /**
     * If $inputData is an array the key 'tmpPath' is needed
     *
     * @param string|array $inputData
     * @param string $outputFile
     */
    public function createPDF($inputData, $outputFile)
    {
        $pdf = new TcpdfFpdi();
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        if (!is_array($inputData['files'])) {
            $inputData['files'] = [$inputData['files']];
        }

        foreach ($inputData['files'] as $file) {
            $box = PageBoundaries::CROP_BOX;
            $groupXObject = true;
            if (is_array($file)) {
                extract($file);
            }

            try {
                $pageCount = $pdf->setSourceFile($file);
            } catch (\Exception $e) {
                echo $e->getMessage() . "\n";
                continue;
            }

            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $pdf->AddPage();
                $tplIdx = $pdf->importPage($pageNo, $box, $groupXObject);
                $pdf->useTemplate($tplIdx, ['adjustPageSize' => true]);
            }
        }

        $pdf->Output($outputFile, 'F');
    }
}
