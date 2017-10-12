<?php

namespace setasign\Fpdi\unit\PdfParser\Type;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\PdfParser\Type\PdfName;
use setasign\Fpdi\PdfParser\Type\PdfString;

class PdfNameTest extends TestCase
{
    public function testCreate()
    {
        $v = PdfName::create('Test');
        $this->assertInstanceOf(PdfName::class, $v);
        $this->assertSame('Test', $v->value);
    }

    /**
     * @expectedException \setasign\Fpdi\PdfParser\Type\PdfTypeException
     * @expectedExceptionCode \setasign\Fpdi\PdfParser\Type\PdfTypeException::INVALID_DATA_TYPE
     */
    public function testEnsureWithInvlaidArgument1()
    {
        PdfName::ensure('test');
    }

    /**
     * @expectedException \setasign\Fpdi\PdfParser\Type\PdfTypeException
     * @expectedExceptionCode \setasign\Fpdi\PdfParser\Type\PdfTypeException::INVALID_DATA_TYPE
     */
    public function testEnsureWithInvlaidArgument2()
    {
        PdfName::ensure(PdfString::create('test'));
    }

    public function testEnsure()
    {
        $a = PdfName::create('F6F6F6');
        $b = PdfName::ensure($a);
        $this->assertSame($a, $b);
    }
}