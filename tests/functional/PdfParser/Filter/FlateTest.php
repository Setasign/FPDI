<?php

namespace setasign\Fpdi\functional\PdfParser\Filter;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\PdfParser\Filter\Flate;

class FlateTest extends TestCase
{
    public function decodeProvider()
    {
        return array(
            ['', ''],
            ["\x78\x9c\xF3\x48\xCC\xC9\xC9\x57\x08\x4F\xCD\x29\x01\x00\x13\xA8\x03\xAD", 'Hallo Welt'],
            ["\x78\x3F\xF3\x48\xCC\xC9\xC9\x57\x08\x4F\xCD\x29\x01\x00", 'Hallo Welt']
        );
    }

    /**
     * @dataProvider decodeProvider
     */
    public function testDecode($in, $expected)
    {
        $filter = new Flate();

        $decoded = $filter->decode($in);

        $this->assertSame($expected, $decoded);
    }

    /**
     * @covers \setasign\Fpdi\PdfParser\Filter\Flate::decode
     * @expectedException \setasign\Fpdi\PdfParser\Filter\FlateException
     * @expectedExceptionCode \setasign\Fpdi\PdfParser\Filter\FlateException::NO_ZLIB
     */
    public function testDecodeWithoutZlib()
    {
        $mock = $this->getMockBuilder(Flate::class)
            ->setMethods(['extensionLoaded'])
            ->getMock();

        $mock->expects($this->once())
            ->method('extensionLoaded')
            ->will($this->returnValue(false));

        $mock->decode("\x78\x9c\xF3\x48\xCC\xC9\xC9\x57\x08\x4F\xCD\x29\x01\x00\x13\xA8\x03\xAD");
    }
}