<?php

namespace setasign\Fpdi\functional\PdfParser\CrossReference;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\PdfParser\CrossReference\FixedReader;
use setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException;
use setasign\Fpdi\PdfParser\PdfParser;
use setasign\Fpdi\PdfParser\StreamReader;

class FixedReaderTest extends TestCase
{
    public function readProvider()
    {
        $data = [];

        $data[] = [
            "0 5\r\n" .
            "0000000000 65535 f\r\n" .
            "0000001000 00000 n\r\n" .
            "0000002000 00000 n\r\n" .
            "0000003000 00000 n\r\n" .
            "0000004000 00000 n\r\n" .
            "trailer\r\n" .
            "<<>>",
            [
                5 => [0, 5]
            ]
        ];

        $data[] = [
            "0 5\r" .
            "0000000000 65535 f \r" .
            "0000001000 00000 n \r" .
            "0000002000 00000 n \r" .
            "0000003000 00000 n \r" .
            "0000004000 00000 n \r" .
            "trailer\r" .
            "<<>>",
            [
                4 => [0, 5]
            ]
        ];

        $data[] = [
            "0 5\n" .
            "0000000000 65535 f \n" .
            "0000001000 00000 n \n" .
            "0000002000 00000 n \n" .
            "0000003000 00000 n \n" .
            "0000004000 00000 n \n" .
            "trailer\n" .
            "<<>>",
            [
                4 => [0, 5]
            ]
        ];

        $data[] = [
            "1 2\n" .
            "0000001000 00000 n \n" .
            "0000002000 00000 n \n" .
            "3 2\n" .
            "0000003000 00000 n \n" .
            "0000004000 00000 n \n" .
            "5 2\n" .
            "0000005000 00000 n \n" .
            "0000006000 00000 n \n" .
            "trailer\n" .
            "<<>>",
            [
                4 =>  [1, 2],
                48 => [3, 2],
                92 => [5, 2]
            ]
        ];

        $data[] = [
            "1 3\n" .
            "0000000000 65535 f\r\n" .
            "0000001000 00000 n\r\n" .
            "0000002000 00000 n\r\n" .
            "trailer\r\n" .
            "<<>>",
            [
                4 => [1, 3]
            ]
        ];

        return $data;
    }

    /**
     * @param $table
     * @param $expectedSubSections
     * @dataProvider readProvider
     */
    public function testRead($table, $expectedSubSections)
    {
        $reader = StreamReader::createByString($table);
        $xref = new FixedReader(new PdfParser($reader));

        $this->assertSame($expectedSubSections, $xref->getSubSections());
    }

    public function readWithInvalidDataProvider()
    {
        $data = [];

        $data[] = [
            'anything but a cross-reference',
            CrossReferenceException::class,
            CrossReferenceException::NO_ENTRIES
        ];

        $data[] = [
            "0 5 \r\n" .
            "0000000000 65535 f \r\n" .
            "0000001000 00000 n \r\n" .
            "0000002000 00000 n \r\n" .
            "0000003000 00000 n \r\n" .
            "0000004000 00000 n \r\n",
            CrossReferenceException::class,
            CrossReferenceException::ENTRIES_TOO_LARGE
        ];

        $data[] = [
            "0 5 \r\n" .
            "0000000000 65535 f\n" .
            "0000001000 00000 n\n" .
            "0000002000 00000 n\n" .
            "0000003000 00000 n\n" .
            "0000004000 00000 n\n",
            CrossReferenceException::class,
            CrossReferenceException::ENTRIES_TOO_SHORT
        ];

        return $data;
    }

    /**
     * @param $table
     * @param $expectedException
     * @param $expectedExceptionCode
     * @dataProvider readWithInvalidDataProvider
     */
    public function testReadWithInvalidData($table, $expectedException, $expectedExceptionCode)
    {
        $this->expectException($expectedException);
        $this->expectExceptionCode($expectedExceptionCode);

        $reader = StreamReader::createByString($table);
        new FixedReader(new PdfParser($reader));
    }

    public function getOffsetProvider()
    {
        $data = [];

        $data[] = [
            "0 5 \r\n" .
            "0000000000 65535 f \n" .
            "0000001000 00000 n \n" .
            "0000002000 00000 n \n" .
            "0000003000 00000 n \n" .
            "0000004000 00000 n \n" .
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
        $xref = new FixedReader(new PdfParser($reader));

        foreach ($expectedOffsets as $objectId => $expectedOffset) {
            $this->assertSame($expectedOffset, $xref->getOffsetFor($objectId));
        }
    }
}
