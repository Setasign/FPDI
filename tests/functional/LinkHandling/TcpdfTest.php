<?php

namespace setasign\Fpdi\functional\LinkHandling;

use ReflectionClass;
use setasign\Fpdi\PdfParser\PdfParser;
use setasign\Fpdi\PdfParser\StreamReader;
use setasign\Fpdi\PdfReader\PageBoundaries;
use setasign\Fpdi\PdfReader\PdfReader;
use setasign\Fpdi\Tcpdf\Fpdi;

class TcpdfTest extends \setasign\Fpdi\functional\LinkHandling\AbstractTest
{
    protected function getInstance($orientation = 'P', $unit = 'mm', $size = 'A4')
    {
        $pdf = new Fpdi($orientation, $unit, $size);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $ref = new ReflectionClass($pdf);
        $prop = $ref->getProperty('tcpdflink');
        $prop->setAccessible(true);
        $prop->setValue($pdf, false);

        return $pdf;
    }

    protected function save($pdf)
    {
        return $pdf->Output('whatever.pdf', 'S');
    }

    public function testImportWithLinkInTemplate()
    {
        $pdf = $this->getInstance();
        $pdf->AddPage();

        $mainTplId = $pdf->startTemplate(100, 200);
        $pdf->setSourceFile(__DIR__ . '/../../_files/pdfs/links/links.pdf');
        $tplId = $pdf->importPage(1, PageBoundaries::CROP_BOX, true, true);
        $pdf->useTemplate($tplId, 10, 10, 100, 200);
        $pdf->endTemplate();

        $pdf->printTemplate($mainTplId, 10, 10, 100, 100);
        $pdf->Rect(10, 10, 100, 100);

        $pdfString = $this->save($pdf);
//        file_put_contents(__DIR__ . '/test.pdf', $pdfString);

        $expectedLinks = [
            [
                'uri' => 'https://www.setasign.com/#1',
                'rect' => [174.934413, 775.28379, 209.675136, 780.475707],
            ],
            [
                'uri' => 'https://www.setasign.com/#2',
                'rect' => [83.263074, 757.549115, 302.547847, 765.180089],
            ],
            [
                'uri' => 'https://www.setasign.com/#4',
                'rect' => [243.216393, 719.545085, 312.273097, 723.891212],
            ],
            [
                'uri' => 'https://www.setasign.com/#5',
                'rect' => [113.210216, 710.252158, 137.670001, 714.598285],
            ],
            [
                'uri' => 'https://demos.setasign.com/?some=(get paramert/with special signs',
                'rect' => [168.403296, 696.293913, 215.377721, 701.485831],
            ],
            [
                'uri' => 'https://www.setasign.com/#3',
                'rect' => [83.739242, 733.46562, 312.504038, 743.304001],
            ]
        ];

        $reader = new PdfReader(new PdfParser(StreamReader::createByString($pdfString)));
        $this->compareExpectedLinks(1, $expectedLinks, $reader);
    }
}
