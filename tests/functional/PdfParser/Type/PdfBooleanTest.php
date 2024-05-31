<?php

namespace setasign\Fpdi\functional\PdfParser\Type;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use setasign\Fpdi\PdfParser\Type\PdfBoolean;

class PdfBooleanTest extends TestCase
{
    public static function createProvider()
    {
        $data = [
            ['true', true],
            ['false', true],
            ['3454', true],
            [false, false],
        ];

        return $data;
    }

    /**
     * @param $in
     * @param $expectedResult
     */
    #[DataProvider('createProvider')]
    public function testCreate($in, $expectedResult)
    {
        $result = PdfBoolean::create($in);
        $this->assertInstanceOf(PdfBoolean::class, $result);
        $this->assertSame($expectedResult, $result->value);
    }
}
