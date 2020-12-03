<?php

namespace setasign\Fpdi\unit\PdfParser\Type;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\PdfParser\Type\PdfNumeric;
use setasign\Fpdi\PdfParser\Type\PdfString;
use setasign\Fpdi\PdfParser\Type\PdfTypeException;

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

    public function testEnsureWithInvlaidArgument1()
    {
        $this->expectException(PdfTypeException::class);
        $this->expectExceptionCode(PdfTypeException::INVALID_DATA_TYPE);
        PdfNumeric::ensure('test');
    }

    public function testEnsureWithInvlaidArgument2()
    {
        $this->expectException(PdfTypeException::class);
        $this->expectExceptionCode(PdfTypeException::INVALID_DATA_TYPE);
        PdfNumeric::ensure(PdfString::create('test'));
    }

    public function testEnsure()
    {
        $a = PdfNumeric::create(123);
        $b = PdfNumeric::ensure($a);
        $this->assertSame($a, $b);
    }
}