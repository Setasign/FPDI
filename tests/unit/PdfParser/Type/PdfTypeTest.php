<?php

namespace setasign\Fpdi\unit\PdfParser\Type;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\PdfParser\PdfParser;
use setasign\Fpdi\PdfParser\Type\PdfIndirectObject;
use setasign\Fpdi\PdfParser\Type\PdfIndirectObjectReference;
use setasign\Fpdi\PdfParser\Type\PdfString;
use setasign\Fpdi\PdfParser\Type\PdfType;

class PdfTypeTest extends TestCase
{
    public function testEnsureWithNonObjectValue()
    {
        $pdfParser = $this->createMock(PdfParser::class);

        $value = PdfString::create('A simple test');
        $result = PdfType::resolve($value, $pdfParser);

        $this->assertSame($value, $result);
    }

    public function testResolveWithIndirectObject()
    {
        $pdfParser = $this->createMock(PdfParser::class);

        $originalValue = PdfString::create('A simple test');
        $value = PdfIndirectObject::create(12, 0, $originalValue);
        $result = PdfType::resolve($value, $pdfParser);

        $this->assertSame($originalValue, $result);
    }

    public function testResolveWithIndirectObjectReference()
    {
        $expectedValue = PdfString::create('A simple test');
        $indirectObject = PdfIndirectObject::create(
            12,
            0,
            $expectedValue
        );

        $mock = $this->getMockBuilder(PdfParser::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIndirectObject'])
            ->getMock();

        $mock->expects($this->once())
            ->method('getIndirectObject')
            ->with(12)
            ->willReturn($indirectObject);

        $value = PdfIndirectObjectReference::create(12, 0);
        $result = PdfType::resolve($value, $mock);

        $this->assertSame($expectedValue, $result);
    }

    public function testResolveWithMoreIndirectObjectReference()
    {
        $expectedValue = PdfString::create('A simple test');
        $indirectObject2 = PdfIndirectObject::create(
            12,
            0,
            $expectedValue
        );

        $indirectObject1 = PdfIndirectObject::create(
            13,
            0,
            PdfIndirectObjectReference::create(12, 0)
        );

        $mock = $this->getMockBuilder(PdfParser::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIndirectObject'])
            ->getMock();

        $mock->expects($this->exactly(2))
            ->method('getIndirectObject')
            ->withConsecutive([13], [12])
            ->willReturnOnConsecutiveCalls($indirectObject1, $indirectObject2);

        $value = PdfIndirectObjectReference::create(13, 0);
        $result = PdfType::resolve($value, $mock);

        $this->assertSame($expectedValue, $result);
    }

    public function testResolveWithMoreIndirectObjectReferenceAndStopParameter()
    {
        $expectedValue = PdfString::create('A simple test');
        $indirectObject2 = PdfIndirectObject::create(
            12,
            0,
            $expectedValue
        );

        $indirectObject1 = PdfIndirectObject::create(
            13,
            0,
            PdfIndirectObjectReference::create(12, 0)
        );

        $mock = $this->getMockBuilder(PdfParser::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIndirectObject'])
            ->getMock();

        $mock->expects($this->exactly(1))
            ->method('getIndirectObject')
            ->withConsecutive([13])
            ->willReturnOnConsecutiveCalls($indirectObject1);

        $value = PdfIndirectObjectReference::create(13, 0);
        $result = PdfType::resolve($value, $mock, true);

        $this->assertSame($indirectObject1, $result);
    }
}