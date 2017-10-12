<?php

namespace setasign\Fpdi\unit\PdfParser\Type;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\PdfParser\Type\PdfHexString;
use setasign\Fpdi\PdfParser\Type\PdfName;

class PdfHexStringTest extends TestCase
{
    public function testCreate()
    {
        $v = PdfHexString::create('F6F6');
        $this->assertInstanceOf(PdfHexString::class, $v);
        $this->assertSame('F6F6', $v->value);
    }

    /**
     * @expectedException \setasign\Fpdi\PdfParser\Type\PdfTypeException
     * @expectedExceptionCode \setasign\Fpdi\PdfParser\Type\PdfTypeException::INVALID_DATA_TYPE
     */
    public function testEnsureWithInvlaidArgument1()
    {
        PdfHexString::ensure('test');
    }

    /**
     * @expectedException \setasign\Fpdi\PdfParser\Type\PdfTypeException
     * @expectedExceptionCode \setasign\Fpdi\PdfParser\Type\PdfTypeException::INVALID_DATA_TYPE
     */
    public function testEnsureWithInvlaidArgument2()
    {
        PdfHexString::ensure(PdfName::create('test'));
    }

    public function testEnsure()
    {
        $a = PdfHexString::create('F6F6F6');
        $b = PdfHexString::ensure($a);
        $this->assertSame($a, $b);
    }
}