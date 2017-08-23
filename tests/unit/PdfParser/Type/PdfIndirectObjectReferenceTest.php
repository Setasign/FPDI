<?php

namespace setasign\Fpdi\unit\PdfParser\Type;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\PdfParser\Type\PdfIndirectObjectReference;
use setasign\Fpdi\PdfParser\Type\PdfName;

class PdfIndirectObjectReferenceTest extends TestCase
{
    public function testCreate()
    {
        $result = PdfIndirectObjectReference::create('234', '2');

        $this->assertInstanceOf(PdfIndirectObjectReference::class, $result);

        $this->assertSame($result->value, 234);
        $this->assertSame($result->generationNumber, 2);
    }

    /**
     * @expectedException \setasign\Fpdi\PdfParser\Type\PdfTypeException
     * @expectedExceptionCode \setasign\Fpdi\PdfParser\Type\PdfTypeException::INVALID_DATA_TYPE
     */
    public function testEnsureWithInvlaidArgument1()
    {
        PdfIndirectObjectReference::ensure('test');
    }

    /**
     * @expectedException \setasign\Fpdi\PdfParser\Type\PdfTypeException
     * @expectedExceptionCode \setasign\Fpdi\PdfParser\Type\PdfTypeException::INVALID_DATA_TYPE
     */
    public function testEnsureWithInvlaidArgument2()
    {
        PdfIndirectObjectReference::ensure(PdfName::create('test'));
    }

    public function testEnsure()
    {
        $a = PdfIndirectObjectReference::create(1, 0);
        $b = PdfIndirectObjectReference::ensure($a);
        $this->assertSame($a, $b);
    }
}