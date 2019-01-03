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
            ["\x78\x3F\xF3\x48\xCC\xC9\xC9\x57\x08\x4F\xCD\x29\x01\x00", 'Hallo Welt'],
            // This is the compressed-crossreference with an invalid checksum
            [
                "\x78\x9c\x62\x64\x60\xd9\xf1\x8b\x81\x81\x91\x81\x65\xe7\x2a\x30\xb5\x6b\x07\x98\xda\x7d\x1e\x4c\xed\x55\x03\x53\xfb\x6a\x19\x18\x00\x00\x00\x00\xff\xff",
                "\x01\x00\x04\xb8\xfa\x00\x00\x01\x00\x04\xb9\xaa\x00\x00\x01\x00\x04\xba\xb8\x00\x00\x01\x00\x04\xbb\xcf\x00\x00\x01\x00\x04\xbd\x26\x00\x00\x01\x00\x04\xbe\x7d\x00\x00"
            ]
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