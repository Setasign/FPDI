<?php

namespace setasign\Fpdi\functional\PdfParser\CrossReference;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\PdfParser\CrossReference\LineReader;
use setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException;
use setasign\Fpdi\PdfParser\PdfParser;
use setasign\Fpdi\PdfParser\StreamReader;

class LineReaderTest extends TestCase
{
    public function readProvider()
    {
        $data = [];

        $data[] = [
            "0 5 \r\n" .
            "0000000000 65535 f\n" .
            "0000001000 00000 n\n" .
            "0000002000 00000 n\n" .
            "0000003000 00000 n\n" .
            "0000004000 00000 n\n" .
            "trailer<</Size 5>>",
            [
                1 => [1000, 0],
                2 => [2000, 0],
                3 => [3000, 0],
                4 => [4000, 0]
            ]
        ];

        $data[] = [
            "0 2\n" .
            "\n" .
            "0000000000 65535 f\r\n" .
            "0000001000 00000 n \n\r" .
            "5 3\n" .
            "\n" .
            "0000005000 00000 n\n" .
            "0000006000 00000 n\n" .
            "0000007000 00000 n\n" .
            "trailer<</Size 1111>>",
            [
                1 => [1000, 0],
                5 => [5000, 0],
                6 => [6000, 0],
                7 => [7000, 0]
            ]
        ];

        $data[] = [
            "1 5 \r\n" .
            "0000000000 65535 f\n" .
            "0000001000 00000 n\n" .
            "0000002000 00001 n\n" .
            "0000000000 00001 f\n" .
            "0000004000 00000 n\n" .
            "trailer\n<<>>",
            [
                2 => [1000, 0],
                3 => [2000, 1],
                5 => [4000, 0]
            ]
        ];

        $data[] = [
            "0 5\n" .
            "0000000000 65535 f\n" .
            "0000001000 00000 n\r\n" .
            "0000002000 00000 n\n" .
            "0000003000 00000 n\r" .
            "0000004000 00000 n\n" .
            "6 5\n" .
            "0000005000 00000 n \n" .
            "0000006000 00000 n \n" .
            "0000007000 00000 n \n" .
            "0000008000 00000 n \n" .
            "0000009000 00000 n \n" .
            "trailer\n<</Size 11>>",
            [
                1 => [1000, 0],
                2 => [2000, 0],
                3 => [3000, 0],
                4 => [4000, 0],
                6 => [5000, 0],
                7 => [6000, 0],
                8 => [7000, 0],
                9 => [8000, 0],
                10 => [9000, 0],
            ],
            10
        ];

        $data[] = [
            "0 0\n" .
            "trailer\n<</Size 1>>",
            []
        ];

        $data[] = [
            "0 4\n" .
            "0000000000 65535 f\n" .
            "0000000001 00001 f \n" .
            "0000000002 00001 f \n" .
            "0000000003 00001 f \n" .
            "trailer\n<</Size 4>>",
            []
        ];

        $data[] = [
            "\ntrailer<<>>",
            []
        ];

        $data[] = [
            "10 12\n" .
            "34 12\n" .
            "trailer<<>>",
            []
        ];

        $data[] = [
            "10 12\n" .
            "0000004000 00000\tn\n" .
            "trailer<<>>",
            []
        ];

        return $data;
    }

    /**
     * @param $table
     * @param $expectedOffsets
     * @param $expectedMaxObjectId
     * @dataProvider readProvider
     */
    public function testRead($table, $expectedOffsets)
    {
        $reader = StreamReader::createByString($table);
        $xref = new LineReader(new PdfParser($reader));

        $this->assertSame($expectedOffsets, $xref->getOffsets());

        $trailerKeyword = $reader->readBytes(7);
    }

    /**
     * @expectedException \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException
     * @expectedExceptionCode \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException::NO_TRAILER_FOUND
     */
    public function testExtractWithInvalidData()
    {
        $reader = StreamReader::createByString('anything but the keyword t r a i l e r.');
        new LineReader(new PdfParser($reader));
    }

    public function parseFlexibleWithInvalidDataProvider()
    {
        $data = [];

        $data[] = [
            'trailer',
            CrossReferenceException::INVALID_DATA
        ];

        $data[] = [
            "0 1\n" .
            "0000004000 00000 m\n" .
            "trailer",
            CrossReferenceException::INVALID_DATA
        ];

        $data[] = [
            "0 1 1 2\n" .
            "trailer",
            CrossReferenceException::INVALID_DATA
        ];

        return $data;
    }

    /**
     * @param $table
     * @dataProvider parseFlexibleWithInvalidDataProvider
     * @expectedException \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException
     */
    public function testParseFlexibleWithInvalidData($table, $expectedExceptionCode)
    {
        $this->expectExceptionCode($expectedExceptionCode);
        $reader = StreamReader::createByString($table);
        new LineReader(new PdfParser($reader));
    }

    public function getOffsetProvider()
    {
        $data = [];

        $data[] = [
            "0 5 \r\n" .
            "0000000000 65535 f\n" .
            "0000001000 00000 n\n" .
            "0000002000 00000 n\n" .
            "0000003000 00000 n\n" .
            "0000004000 00000 n\n" .
            "trailer<</Size 5>>",
            [
                0 => false,
                1 => 1000,
                2 => 2000,
                3 => 3000,
                4 => 4000
            ]
        ];

        return $data;
    }

    /**
     * @param $table
     * @param $expectedOffsets
     * @dataProvider getOffsetProvider
     */
    public function testGetOffset($table, $expectedOffsets)
    {
        $reader = StreamReader::createByString($table);
        $xref = new LineReader(new PdfParser($reader));

        foreach ($expectedOffsets as $objectId => $expectedOffset) {
            $this->assertSame($expectedOffset, $xref->getOffsetFor($objectId));
        }
    }
}