<?php

namespace setasign\Fpdi\unit\PdfParser\Type;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\PdfParser\Type\PdfBoolean;
use setasign\Fpdi\PdfParser\Type\PdfName;

class PdfBooleanTest extends TestCase
{
    public function testCreate()
    {
        $v = PdfBoolean::create(true);
        $this->assertInstanceOf(PdfBoolean::class, $v);
        $this->assertSame(true, $v->value);

        $v = PdfBoolean::create(false);
        $this->assertInstanceOf(PdfBoolean::class, $v);
        $this->assertSame(false, $v->value);
    }

    /**
     * @expectedException \setasign\Fpdi\PdfParser\Type\PdfTypeException
     * @expectedExceptionCode \setasign\Fpdi\PdfParser\Type\PdfTypeException::INVALID_DATA_TYPE
     */
    public function testEnsureWithInvlaidArgument1()
    {
        PdfBoolean::ensure('test');
    }

    /**
     * @expectedException \setasign\Fpdi\PdfParser\Type\PdfTypeException
     * @expectedExceptionCode \setasign\Fpdi\PdfParser\Type\PdfTypeException::INVALID_DATA_TYPE
     */
    public function testEnsureWithInvlaidArgument2()
    {
        PdfBoolean::ensure([PdfName::class, 'test']);
    }

    public function testEnsure()
    {
        $a = PdfBoolean::create(true);
        $b = PdfBoolean::ensure($a);
        $this->assertSame($a, $b);
    }
}