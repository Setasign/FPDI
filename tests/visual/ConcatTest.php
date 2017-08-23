<?php

namespace setasign\Fpdi\visual;

use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader\PageBoundaries;

class ConcatTest extends VisualTestCase
{
    public function createProvider()
    {
        $path = __DIR__ . '/..';
        $data = [
            0 => [
                [
                    'files' => [
                        $path . '/_files/pdfs/transparency/ex74.pdf',
                        $path . '/_files/pdfs/stamps/ENU/StandardBusiness.pdf',
                        $path . '/_files/pdfs/tektown/Logo.pdf',
                        $path . '/_files/pdfs/boxes/All2.pdf'
                    ],
                    'tmpPath' => '0'
                ],
                0.5,
                36
            ],

            'rotated' => [
                [
                    'files' => [
                        0 => $path . '/_files/pdfs/rotated/90.pdf',
                        1 => $path . '/_files/pdfs/rotated/-90.pdf',
                        2 => $path . '/_files/pdfs/rotated/180.pdf',
                        3 => $path . '/_files/pdfs/rotated/-180.pdf',
                        4 => $path . '/_files/pdfs/rotated/270.pdf',
                        5 => $path . '/_files/pdfs/rotated/-270.pdf',
                        6 => $path . '/_files/pdfs/rotated/360.pdf',
                        7 => $path . '/_files/pdfs/rotated/-360.pdf',
                        8 => $path . '/_files/pdfs/rotated/450.pdf',
                        9 => $path . '/_files/pdfs/rotated/-450.pdf',
                        10 => $path . '/_files/pdfs/rotated/all.pdf',
                    ],
                    'tmpPath' => 'rotated'
                ],
                0.5,
                36
            ],
            'boxes/All2' => [
                [
                    'files' => [
                        ['file' => $path . '/_files/pdfs/boxes/All2.pdf'],
                        ['file' => $path . '/_files/pdfs/boxes/All2.pdf', 'box' => PageBoundaries::MEDIA_BOX],
                        ['file' => $path . '/_files/pdfs/boxes/All2.pdf', 'box' => PageBoundaries::BLEED_BOX],
                        ['file' => $path . '/_files/pdfs/boxes/All2.pdf', 'box' => PageBoundaries::TRIM_BOX],
                        ['file' => $path . '/_files/pdfs/boxes/All2.pdf', 'box' => PageBoundaries::ART_BOX],
                    ],
                    'tmpPath' => 'boxes/All2'
                ],
                0.1,
                36
            ],
            'boxes/[1000 500 -1000 -500]' => [
                [
                    'files' => [
                        $path . '/_files/pdfs/boxes/[1000 500 -1000 -500].pdf'
                    ],
                    'tmpPath' => 'boxes/[1000 500 -1000 -500]'
                ],
                0.1,
                36
            ],
            'boxes/[1000 500 -1000 -500]-R90' => [
                [
                    'files' => [
                        $path . '/_files/pdfs/boxes/[1000 500 -1000 -500]-R90.pdf'
                    ],
                    'tmpPath' => 'boxes/[1000 500 -1000 -500]-R90'
                ],
                0.1,
                36
            ],
            'boxes/[-1000 -1000 -500 -500]' => [
                [
                    'files' => [
                        $path . '/_files/pdfs/boxes/[-1000 -1000 -500 -500].pdf'
                    ],
                    'tmpPath' => 'boxes/[-1000 -1000 -500 -500]'
                ],
                0.1,
                36
            ],

            'flate-and-hex' => [
                [
                    'files' => [
                        $path . '/_files/pdfs/filters/multiple/flate-and-hex.pdf'
                    ],
                    'tmpPath' => 'flate-and-hex'
                ],
                0.1,
                36
            ],
            '0 0 R' => [
                [
                    'files' => [
                        $path . '/_files/pdfs/specials/0 0 R/template_pracovny_prikaz7-ooo-a.pdf'
                    ],
                    'tmpPath' => 'specials/0 0 R'
                ],
                0.1,
                36
            ]
        ];

        #return [$data['[1000 500 -1000 -500]']];

        return $data;
    }

    /**
     * If $inputData is an array the key 'tmpPath' is needed
     *
     * @param string|array $inputData
     * @param string $outputFile
     */
    public function createPDF($inputData, $outputFile)
    {
        $pdf = new Fpdi();

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
