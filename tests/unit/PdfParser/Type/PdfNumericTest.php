<?php

namespace setasign\Fpdi\unit\PdfParser\Type;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\PdfParser\Type\PdfNumeric;
use setasign\Fpdi\PdfParser\Type\PdfString;

class PdfNumericTest extends TestCase
{
    public function parseProvider()
    {
        $data = [
            [
                '1', 1
            ],
            [
                '13', 13
            ],
            [
                '0789', 789
            ],
            [
                '1.23', 1.23
            ],
            [
                '01.230', 1.23
            ],
            [
                '12345.678919', 12345.678919
            ],
            [
                '12300000000', 12300000000
            ],
            [
                '123', 123
            ],
            [
                '43445', 43445
            ],
            [
                '+17', 17
            ],
            [
                '-98', -98
            ],
            [
                '0', 0
            ],
            [
                '34.5', 34.5
            ],
            [
                '-3.62', -3.62
            ],
            [
                '+123.6', 123.6
            ],
            [
                '4.', 4.
            ],
            [
                '-.002', -.002
            ],
            [
                0.0, 0.0
            ]
        ];

        return $data;
    }

    /**
     * @param $in
     * @param $expectedResult
     * @dataProvider parseProvider
     */
    public function testCreate($in, $expectedResult)
    {
        $result = PdfNumeric::create($in);

        $this->assertInstanceOf(PdfNumeric::class, $result);
        $this->assertSame($expectedResult, $result->value);
    }

    /**
     * @expectedException \setasign\Fpdi\PdfParser\Type\PdfTypeException
     * @expectedExceptionCode \setasign\Fpdi\PdfParser\Type\PdfTypeException::INVALID_DATA_TYPE
     */
    public function testEnsureWithInvlaidArgument1()
    {
        PdfNumeric::ensure('test');
    }

    /**
     * @expectedException \setasign\Fpdi\PdfParser\Type\PdfTypeException
     * @expectedExceptionCode \setasign\Fpdi\PdfParser\Type\PdfTypeException::INVALID_DATA_TYPE
     */
    public function testEnsureWithInvlaidArgument2()
    {
        PdfNumeric::ensure(PdfString::create('test'));
    }

    public function testEnsure()
    {
        $a = PdfNumeric::create(123);
        $b = PdfNumeric::ensure($a);
        $this->assertSame($a, $b);
    }
}