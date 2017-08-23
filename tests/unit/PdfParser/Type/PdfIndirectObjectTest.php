<?php

namespace setasign\Fpdi\unit\PdfParser\Type;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\PdfParser\Type\PdfArray;
use setasign\Fpdi\PdfParser\Type\PdfIndirectObject;
use setasign\Fpdi\PdfParser\Type\PdfName;
use setasign\Fpdi\PdfParser\Type\PdfNumeric;
use setasign\Fpdi\PdfParser\Type\PdfString;

class PdfIndirectObjectTest extends TestCase
{
    public function testCreate()
    {
        $value = PdfArray::create([
            PdfNumeric::create(123), PdfString::create('ABCDE')
        ]);
        $result = PdfIndirectObject::create('234', '2', $value);

        $this->assertInstanceOf(PdfIndirectObject::class, $result);

        $this->assertSame($result->value, $value);
        $this->assertSame($result->objectNumber, 234);
        $this->assertSame($result->generationNumber, 2);
    }

    /**
     * @expectedException \setasign\Fpdi\PdfParser\Type\PdfTypeException
     * @expectedExceptionCode \setasign\Fpdi\PdfParser\Type\PdfTypeException::INVALID_DATA_TYPE
     */
    public function testEnsureWithInvlaidArgument1()
    {
        PdfIndirectObject::ensure('test');
    }

    /**
     * @expectedException \setasign\Fpdi\PdfParser\Type\PdfTypeException
     * @expectedExceptionCode \setasign\Fpdi\PdfParser\Type\PdfTypeException::INVALID_DATA_TYPE
     */
    public function testEnsureWithInvlaidArgument2()
    {
        PdfIndirectObject::ensure(PdfName::create('test'));
    }

    public function testEnsure()
    {
        $a = PdfIndirectObject::create(1, 0, PdfNumeric::create(1));
        $b = PdfIndirectObject::ensure($a);
        $this->assertSame($a, $b);
    }
}