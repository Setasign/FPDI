<?php

namespace setasign\Fpdi\unit\PdfParser\Type;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\PdfParser\Type\PdfIndirectObjectReference;
use setasign\Fpdi\PdfParser\Type\PdfName;
use setasign\Fpdi\PdfParser\Type\PdfTypeException;

class PdfIndirectObjectReferenceTest extends TestCase
{
    public function testCreate()
    {
        $result = PdfIndirectObjectReference::create('234', '2');

        $this->assertInstanceOf(PdfIndirectObjectReference::class, $result);

        $this->assertSame($result->value, 234);
        $this->assertSame($result->generationNumber, 2);
    }

    public function testEnsureWithInvlaidArgument1()
    {
        $this->expectException(PdfTypeException::class);
        $this->expectExceptionCode(PdfTypeException::INVALID_DATA_TYPE);
        PdfIndirectObjectReference::ensure('test');
    }

    public function testEnsureWithInvlaidArgument2()
    {
        $this->expectException(PdfTypeException::class);
        $this->expectExceptionCode(PdfTypeException::INVALID_DATA_TYPE);
        PdfIndirectObjectReference::ensure(PdfName::create('test'));
    }

    public function testEnsure()
    {
        $a = PdfIndirectObjectReference::create(1, 0);
        $b = PdfIndirectObjectReference::ensure($a);
        $this->assertSame($a, $b);
    }
}