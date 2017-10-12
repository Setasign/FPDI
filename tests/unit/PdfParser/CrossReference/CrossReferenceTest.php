<?php

namespace setasign\Fpdi\unit\PdfParser\CrossReference;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\PdfParser\CrossReference\CrossReference;
use setasign\Fpdi\PdfParser\CrossReference\FixedReader;

class CrossReferenceTest extends TestCase
{
    /**
     * This test ensures that the first table (last table in the document) with an object id returns the offset
     * directly and the other tables were not queried.
     */
    public function testGetOffsetFor()
    {
        $tableMock1 = $this->getMockBuilder(FixedReader::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOffsetFor'])
            ->getMock();

        $tableMock1->expects($this->once())
            ->method('getOffsetFor')
            ->with(123)
            ->willReturn(1000);

        $tableMock2 = $this->getMockBuilder(FixedReader::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOffsetFor'])
            ->getMock();

        $tableMock2->expects($this->never())
            ->method('getOffsetFor');

        $mock = $this->getMockBuilder(CrossReference::class)
            ->disableOriginalConstructor()
            ->setMethods(['getReaders'])
            ->getMock();

        $mock->expects($this->once())
            ->method('getReaders')
            ->willReturn([$tableMock1, $tableMock2]);

        $this->assertSame(1000, $mock->getOffsetFor(123));
    }

    /**
     * @expectedException \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException
     * @expectedExceptionCode \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException::OBJECT_NOT_FOUND
     */
    public function testGetIndirectObjectWithInvalidObjectId()
    {
        $mock = $this->getMockBuilder(CrossReference::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOffsetFor'])
            ->getMock();

        $mock->expects($this->once())
            ->method('getOffsetFor')
            ->with(123)
            ->willReturn(false);

        $mock->getIndirectObject(123);
    }
}