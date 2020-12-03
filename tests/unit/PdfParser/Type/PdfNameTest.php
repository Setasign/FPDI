<?php

namespace setasign\Fpdi\unit\PdfParser\Type;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\PdfParser\Type\PdfName;
use setasign\Fpdi\PdfParser\Type\PdfString;
use setasign\Fpdi\PdfParser\Type\PdfTypeException;

class PdfNameTest extends TestCase
{
    public function testCreate()
    {
        $v = PdfName::create('Test');
        $this->assertInstanceOf(PdfName::class, $v);
        $this->assertSame('Test', $v->value);
    }

    public function testEnsureWithInvlaidArgument1()
    {
        $this->expectException(PdfTypeException::class);
        $this->expectExceptionCode(PdfTypeException::INVALID_DATA_TYPE);
        PdfName::ensure('test');
    }

    public function testEnsureWithInvlaidArgument2()
    {
        $this->expectException(PdfTypeException::class);
        $this->expectExceptionCode(PdfTypeException::INVALID_DATA_TYPE);
        PdfName::ensure(PdfString::create('test'));
    }

    public function testEnsure()
    {
        $a = PdfName::create('F6F6F6');
        $b = PdfName::ensure($a);
        $this->assertSame($a, $b);
    }

    public function unescapeProvider()
    {
        return [
            ['1.5'                , '1#2E5'],
            ['#Test'              , '#23Test'],
            ['Adobe Green'        , 'Adobe#20Green'],
            ['PANTONE 5757 CV'    , 'PANTONE#205757#20CV'],
            ['paired()parentheses', 'paired#28#29parentheses'],
            ['Abc'                , 'Abc']
        ];
    }

    /**
     * @param $expectedResult
     * @param $escaped
     * @dataProvider unescapeProvider
     */
    public function testUnescape($expectedResult, $escaped)
    {
        $this->assertEquals($expectedResult, PdfName::unescape($escaped));
    }
}