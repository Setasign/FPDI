<?php

namespace setasign\Fpdi\functional\PdfReader;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\PdfParser\PdfParser;
use setasign\Fpdi\PdfParser\StreamReader;
use setasign\Fpdi\PdfReader\DataStructure\Rectangle;
use setasign\Fpdi\PdfReader\PdfReader;

class PageTest extends TestCase
{
    public function getExternalLinksProvider()
    {
        return [
            [
                __DIR__ . '/../../_files/pdfs/links/links.pdf',
                [
                    1 => [
                        [
                            'uri' => 'https://www.setasign.com/#1',
                            'rect' => new Rectangle(248.319, 770.353, 321.278, 785.773),
                            'quadPoints' => []
                        ],
                        [
                            'uri' => 'https://www.setasign.com/#2',
                            'rect' => new Rectangle(55.8, 717.681, 516.32, 740.345),
                            'quadPoints' => []
                        ],
                        [
                            'uri' => 'https://www.setasign.com/#4',
                            'rect' => new Rectangle(391.718, 604.809, 536.744, 617.717),
                            'quadPoints' => []
                        ],
                        [
                            'uri' => 'https://www.setasign.com/#5',
                            'rect' => new Rectangle(118.692, 577.209, 170.06, 590.117),
                            'quadPoints' => []
                        ],
                        [
                            'uri' => 'https://demos.setasign.com/?some=(get paramert/with special signs',
                            'rect' => new Rectangle(234.603, 535.753, 333.254, 551.173),
                            'quadPoints' => []
                        ],
                        [
                            'uri' => 'https://www.setasign.com/#3',
                            'quadPoints' => [
                                507.913, 659.953, 537.229, 659.953, 537.229, 675.373, 507.913, 675.373,
                                56.8, 646.153, 82.0958, 646.153, 82.0958, 661.573, 56.8, 661.573
                            ]
                        ]
                    ],
                    2 => [
                        [
                            'uri' => 'https://www.setasign.com',
                            'quadPoints' => [
                                474.12, 590.953, 500.412, 590.953, 500.412, 606.373, 474.12, 606.373,
                                56.8, 577.153, 169.059, 577.153, 169.059, 592.573, 56.8, 592.573
                            ]
                        ]
                    ]
                ]
            ],
            [
                __DIR__ . '/../../_files/pdfs/links/rotated-pages.pdf',
                [
                    1 => [
                        [
                            'uri' => 'https://www.setasign.com',
                            'rect' => new Rectangle(414.5609999999999, 151.34099999999995, 447.8809999999999, 297.64)
                        ]
                    ]
                ]
            ],
            [
                __DIR__ . '/../../_files/pdfs/links/annotations-with-invalid-references.pdf',
                [
                    1 => [
                        [
                            'uri' => 'https://www.setasign.com/#1',
                            'rect' => new Rectangle(20, 20, 100, 200)
                        ],
                        [
                            'uri' => 'https://www.setasign.com/#2',
                            'rect' => new Rectangle(140, 140, 100, 200)
                        ]
                    ]
                ]
            ],
            [
                __DIR__ . '/../../_files/pdfs/links/invalid-annots-reference.pdf',
                [
                    1 => []
                ]
            ]
        ];
    }

    /**
     * @dataProvider getExternalLinksProvider
     */
    public function testGetExternalLinks($path, $expectedData)
    {
        $stream = StreamReader::createByFile($path);
        $parser = new PdfParser($stream);

        $pdfReader = new PdfReader($parser);

        foreach ($expectedData as $pageNo => $expectedDataPerPage) {
            $page = $pdfReader->getPage($pageNo);
            $data = $page->getExternalLinks();

            $this->assertEquals(count($data), count($expectedDataPerPage));
            foreach ($expectedDataPerPage as $no => $expectedPageData) {
                foreach ($expectedPageData as $key => $value) {
                    $this->assertEquals($value, $data[$no][$key]);
                }
            }
        }
    }
}
