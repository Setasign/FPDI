<?php

namespace setasign\Fpdi\unit\PdfReader\DataStructure;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\PdfReader\DataStructure\Rectangle;

class RectangleTest extends TestCase
{
    public function dataProvider()
    {
        return [
            [
                [0, 0, 100, 200],
                [0, 0, 100, 200],
                100, 200
            ],
            [
                [100, 200, 0, 0],
                [0, 0, 100, 200],
                100, 200
            ],
            [
                [-100, -200, 0, 0],
                [-100, -200, 0, 0],
                100,
                200
            ],
            [
                [-100, -200, 100, 200],
                [-100, -200, 100, 200],
                200, 400
            ],
            [
                [-50, -50, -200, -200],
                [-200, -200, -50, -50],
                150,
                150
            ],
            [
                [100, 100, -50, -50],
                [-50, -50, 100, 100],
                150,
                150
            ]
        ];
    }

    /**
     * @param $array
     * @param $expectedWidth
     * @param $expectedHeight
     * @dataProvider dataProvider
     */
    public function testGetterAndSetters($array, $expectedArray, $expectedWidth, $expectedHeight)
    {
        list($ax, $ay, $bx, $by) = $array;
        $rect = new Rectangle($ax, $ay, $bx, $by);

        list($llx, $lly, $urx, $ury) = $expectedArray;

        $this->assertSame($expectedWidth, $rect->getWidth());
        $this->assertSame($expectedHeight, $rect->getHeight());
        $this->assertSame($llx, $rect->getLlx());
        $this->assertSame($lly, $rect->getLly());
        $this->assertSame($urx, $rect->getUrx());
        $this->assertSame($ury, $rect->getUry());
        $this->assertSame($expectedArray, $rect->toArray());
    }
}
