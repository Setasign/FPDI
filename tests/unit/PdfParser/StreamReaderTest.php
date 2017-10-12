<?php

namespace setasign\Fpdi\unit\PdfParser;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\PdfParser\StreamReader;

class StreamReaderTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructorWithNonStream()
    {
        /** @noinspection PhpParamsInspection */
        new StreamReader('test');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructorWithNonSeekableStream()
    {
        $h = fopen('php://output', 'rb');
        try {
            new StreamReader($h);
        } finally {
            fclose($h);
        }
    }

    public function testConstructor()
    {
        $resource = fopen('php://temp', 'r+b');
        fwrite($resource, 'Hallo Welt');
        rewind($resource);

        $streamReader = new StreamReader($resource, true);
        $this->assertSame($resource, $streamReader->getStream());
    }

    public function testDestructWithClosingStream()
    {
        $resource = fopen('php://temp', 'r+b');
        fwrite($resource, 'Hallo Welt');
        rewind($resource);

        $streamReader = new StreamReader($resource, true);
        $this->assertSame('stream', get_resource_type($resource));
        unset($streamReader);
        $this->assertSame('Unknown', get_resource_type($resource));
    }

    public function testDestructWithoutClosingStream()
    {
        $resource = fopen('php://temp', 'r+b');
        fwrite($resource, 'Hallo Welt');
        rewind($resource);

        $streamReader = new StreamReader($resource, false);
        $this->assertSame('stream', get_resource_type($resource));
        unset($streamReader);
        $this->assertSame('stream', get_resource_type($resource));
    }

    public function testCreateByString()
    {
        $string = 'Hallo Welt';
        $streamReader = StreamReader::createByString($string);
        $this->assertSame('Hallo Welt', $streamReader->getBuffer());
        $resource = $streamReader->getStream();
        $this->assertSame('stream', get_resource_type($resource));
        unset($streamReader);
        $this->assertSame('Unknown', get_resource_type($resource));
    }

    public function testCreateByFile()
    {
        $streamReader = StreamReader::createByFile(__DIR__ . '/_files/streamReader.txt');
        $this->assertSame('Hallo Welt', $streamReader->readLine());
        $this->assertSame('Das ist ein Test', $streamReader->readLine());
        $this->assertSame('!', $streamReader->readLine());
        $this->assertFalse($streamReader->readLine());
        $resource = $streamReader->getStream();
        $this->assertSame('stream', get_resource_type($resource));
        unset($streamReader);
        $this->assertSame('Unknown', get_resource_type($resource));
    }

    public function stringProvider()
    {
        $longContent = str_repeat('abcd', 10000);
        $moreLinesContent = str_repeat("dasdwaa\nasdasd", 1000);
        $moreLines2Content = str_repeat("dasdwaa\r\nasdasd", 1000);

        return [
            [$longContent],
            [$moreLinesContent],
            [$moreLines2Content]
        ];
    }

    /**
     * @dataProvider stringProvider
     */
    public function testGetTotalLength($content)
    {
        $streamReader = StreamReader::createByString($content);
        $this->assertSame(strlen($content), $streamReader->getTotalLength());
    }

    public function resetProvider()
    {
        return [
            ['offset' => null, 'length' =>  10],
            ['offset' =>   10, 'length' => 100],
            ['offset' =>  100, 'length' => 100],
            ['offset' =>    0, 'length' =>  10],
            ['offset' =>   -5, 'length' =>  10],
            ['offset' =>    0, 'length' =>   0]
        ];
    }

    /**
     * @dataProvider resetProvider
     */
    public function testReset($offset, $length)
    {
        $content = str_repeat("dasdwaa\r\nasdasd", 1000);
        $streamReader = StreamReader::createByString($content);
        $streamReader->reset($offset, $length);
        $this->assertSame(substr($content, $offset, $length), $streamReader->getBuffer());
    }

    /**
     * @dataProvider stringProvider
     */
    public function testIncreaseLength($content)
    {
        $streamReader = StreamReader::createByString($content);

        $streamReader->reset(0, 100);
        $res = $streamReader->increaseLength(10); // increases by 100
        $this->assertTrue($res);

        $buffer = $streamReader->getBuffer();

        $this->assertEquals(substr($content, 0, 200), $buffer);

        $res = $streamReader->increaseLength(strlen($content) - 200);
        $this->assertTrue($res);

        $res = $streamReader->increaseLength(1);
        $this->assertFalse($res);
    }

    /**
     * @dataProvider stringProvider
     */
    public function testGetOffsetAndSetOffset($content)
    {
        $streamReader = StreamReader::createByString($content);
        $this->assertEquals(0, $streamReader->getOffset());

        $streamReader->setOffset(1);
        $this->assertEquals(1, $streamReader->getOffset());

        $streamReader->setOffset(88);
        $this->assertEquals(88, $streamReader->getOffset());
    }

    /**
     * @expectedException \OutOfRangeException
     */
    public function testSetOffsetWithInvalidArgumentTooBigNumbers()
    {
        $streamReader = StreamReader::createByString('Hallo Welt');
        $streamReader->setOffset(100);
    }

    /**
     * @expectedException \OutOfRangeException
     */
    public function testSetOffsetWithInvalidArgumentNegativeNumbers()
    {
        $streamReader = StreamReader::createByString('Hallo Welt');
        $streamReader->setOffset(-1);
    }

    public function testAddOffset()
    {
        $streamReader = StreamReader::createByString('Hallo Welt');
        $streamReader->addOffset(1);
        $this->assertEquals(1, $streamReader->getOffset());

        $streamReader->addOffset(4);
        $this->assertEquals(5, $streamReader->getOffset());
    }

    /**
     * @expectedException \OutOfRangeException
     */
    public function testAddOffsetWithInvalidArgument()
    {
        $streamReader = StreamReader::createByString(str_repeat('abcdefghijklmnopqurstuvwxyz', 20));
        $streamReader->reset(0, 100);
        $streamReader->addOffset(101);
    }

    /**
     * @dataProvider stringProvider
     */
    public function testGetBuffer($content)
    {
        $streamReader = StreamReader::createByString($content);
        $streamReader->reset(0, 100);

        $this->assertEquals(substr($content, 0, 100), $streamReader->getBuffer());

        $streamReader->setOffset(10);
        $this->assertEquals(substr($content, 10, 90), $streamReader->getBuffer());

        $this->assertEquals(substr($content, 0, 100), $streamReader->getBuffer(false));

        $streamReader->setOffset(100);
        $streamReader->ensureContent();
        $this->assertEquals(substr($content, 0, 200), $streamReader->getBuffer(false));
    }

    /**
     * @dataProvider stringProvider
     */
    public function testGetBufferLength($content)
    {
        $streamReader = StreamReader::createByString($content);
        $streamReader->reset(0, 100);
        $this->assertEquals(100, $streamReader->getBufferLength());

        $streamReader->reset(0, 200);
        $this->assertEquals(200, $streamReader->getBufferLength());

        $streamReader->setOffset(50);
        $this->assertEquals(200, $streamReader->getBufferLength());
        $this->assertEquals(150, $streamReader->getBufferLength(true));
    }

    /**
     * @dataProvider stringProvider
     */
    public function testReadLine($content)
    {
        $chars = substr($content, 0, 100);
        $lines = preg_split("/(\n)|(\r\n)/", $chars);
        $firstLine = array_shift($lines);

        $streamReader = StreamReader::createByString($content);
        $this->assertEquals($firstLine, $streamReader->readLine(100));

        $streamReader->reset(100, 100);

        $chars = substr($content, 100, 100);
        $lines = preg_split("/(\n)|(\r\n)/", $chars);
        $firstLine = array_shift($lines);
        $this->assertEquals($firstLine, $streamReader->readLine(100));
    }

    public function testReadLineWithEmptyStream()
    {
        $streamReader = StreamReader::createByString('');
        $this->assertFalse($streamReader->readLine(100));
    }

    /**
     * @dataProvider stringProvider
     */
    public function testGetByte($content)
    {
        $streamReader = StreamReader::createByString($content);
        $streamReader->reset(0, 100);

        $byte = $streamReader->getByte();
        $this->assertEquals($content[0], $byte);

        $byte = $streamReader->getByte();
        $this->assertEquals($content[0], $byte);

        $byte = $streamReader->getByte(5);
        $this->assertEquals($content[5], $byte);

        $streamReader->addOffset(1);
        $byte = $streamReader->getByte();
        $this->assertEquals($content[1], $byte);

        $byte = $streamReader->getByte(6);
        $this->assertEquals($content[6], $byte);

        $byte = $streamReader->getByte(102);
        $this->assertEquals($content[102], $byte);

        $byte = $streamReader->getByte($streamReader->getTotalLength());
        $this->assertFalse($byte);
    }

    /**
     * @dataProvider stringProvider
     */
    public function testReadByte($content)
    {
        $streamReader = StreamReader::createByString($content);
        $streamReader->reset(0, 100);

        $this->assertEquals($content[0], $streamReader->readByte());
        $this->assertEquals($content[1], $streamReader->readByte());
        $this->assertEquals($content[5], $streamReader->readByte(5));

        $streamReader->addOffset(1);
        $this->assertEquals($content[7], $streamReader->readByte());

        $this->assertEquals($content[6], $streamReader->readByte(6));
        $this->assertEquals($content[102], $streamReader->readByte(102));
        $this->assertFalse($streamReader->readByte($streamReader->getTotalLength()));
    }

    /**
     * @dataProvider stringProvider
     */
    public function testReadByteWithPos($content)
    {
        $streamReader = StreamReader::createByString($content);
        $streamReader->reset(0, 10);
        $this->assertEquals($content[50], $streamReader->readByte(50));

        $streamReader->reset(0, 10);
        $this->assertEquals($content[8], $streamReader->readByte(8));

        $streamReader->reset(100);
        $this->assertEquals($content[8], $streamReader->readByte(8));

        $streamReader->reset(100);
        $this->assertEquals($content[0], $streamReader->readByte(0));

        $streamReader->reset(strlen($content) - 1);
        $this->assertFalse($streamReader->readByte(strlen($content)));
    }

    /**
     * @dataProvider stringProvider
     */
    public function testReadBytes($content)
    {
        $streamReader = StreamReader::createByString($content);
        $streamReader->reset(0, 100);

        $this->assertEquals($content[0], $streamReader->readBytes(1));
        $this->assertEquals($content[1], $streamReader->readBytes(1));
        $this->assertEquals(substr($content, 2, 2), $streamReader->readBytes(2));
        $this->assertEquals(substr($content, 4, 10), $streamReader->readBytes(10));
        $this->assertEquals(substr($content, 14, 10), $streamReader->readBytes(10));
        $this->assertEquals(substr($content, 24, 100), $streamReader->readBytes(100));

        $streamReader->reset($streamReader->getTotalLength() - 50, 50);

        $this->assertFalse($streamReader->readBytes(100));

        //Test whether the end is readable
        $streamReader->reset($streamReader->getTotalLength() - 50, 50);
        $this->assertEquals(substr($content, $streamReader->getTotalLength()-50), $streamReader->readBytes(50));
    }

    /**
     * @dataProvider stringProvider
     */
    public function testReadBytesWithPos($content)
    {
        $streamReader = StreamReader::createByString($content);
        $streamReader->reset(0, 10);
        $this->assertEquals(substr($content, 50, 5), $streamReader->readBytes(5, 50));

        $streamReader->reset(0, 10);
        $this->assertEquals(substr($content, 8, 5), $streamReader->readBytes(5, 8));

        $streamReader->reset(100);
        $this->assertEquals(substr($content, 8, 5), $streamReader->readBytes(5, 8), 'Test 3');

        $streamReader->reset(100);
        $this->assertEquals(substr($content, 0, 5), $streamReader->readBytes(5, 0), 'Test 4');
    }

    /**
     * @dataProvider stringProvider
     */
    public function testEnsureContent($content)
    {
        $streamReader = StreamReader::createByString($content);
        $this->assertTrue($streamReader->ensureContent());
        $streamReader->reset(0, 100);

        $streamReader->setOffset(100);

        $this->assertEquals('', $streamReader->getBuffer());
        $this->assertTrue($streamReader->ensureContent());
        $this->assertEquals(substr($content, 100, 100), $streamReader->getBuffer());
    }

    public function testEnsureContentWithNoContent()
    {
        $streamReader = StreamReader::createByString('');
        $this->assertFalse($streamReader->ensureContent());
    }

    public function testGetPosition()
    {
        $streamReader = StreamReader::createByString(str_repeat('abcdefghijklmnopqurstuvwxyz', 20));
        $streamReader->reset(0, 100);
        $this->assertEquals(0, $streamReader->getPosition());

        $streamReader->reset(10, 100);
        $this->assertEquals(10, $streamReader->getPosition());
    }
}
