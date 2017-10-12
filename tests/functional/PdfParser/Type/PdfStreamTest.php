<?php

namespace setasign\Fpdi\functional\PdfParser\Type;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\PdfParser\PdfParser;
use setasign\Fpdi\PdfParser\StreamReader;
use setasign\Fpdi\PdfParser\Type\PdfBoolean;
use setasign\Fpdi\PdfParser\Type\PdfDictionary;
use setasign\Fpdi\PdfParser\Type\PdfNull;
use setasign\Fpdi\PdfParser\Type\PdfNumeric;
use setasign\Fpdi\PdfParser\Type\PdfStream;

class PdfStreamTest extends TestCase
{
    public function testParse()
    {
        $in = "123 0 obj\n<</Length 5>>\nstream\nHello\nendstream\nendobj";

        $stream = StreamReader::createByString($in);

        // set position and prepare dictionary (equals to result)
        $stream->setOffset(30);
        $this->assertSame("\n", $stream->getByte()); // this is the \n after the stream keywords

        $dict = PdfDictionary::create([
            'Length' => PdfNumeric::create(5)
        ]);

        $result = PdfStream::parse($dict, $stream);

        $this->assertSame($dict, $result->value);
        $this->assertSame('Hello', $result->getStream());
    }

    public function testParse2()
    {
        $in = "123 0 obj\r\n<</Length 5>>\r\nstream\r\nHello\r\nendstream\r\nendobj";

        $stream = StreamReader::createByString($in);

        // set position and prepare dictionary (equals to result)
        $stream->setOffset(32);
        $this->assertSame("\r", $stream->getByte()); // this is the \r after the stream keywords

        $dict = PdfDictionary::create([
            'Length' => PdfNumeric::create(5)
        ]);

        $result = PdfStream::parse($dict, $stream);

        $this->assertSame($dict, $result->value);
        $this->assertSame('Hello', $result->getStream());
    }

    public function testParse3()
    {
        $streamContent = str_repeat('Hello ', 2000);
        $in = "123 0 obj\r\n<</Length 12000>>\r\nstream\r\n$streamContent\r\nendstream\r\nendobj";

        $stream = StreamReader::createByString($in);

        // set position and prepare dictionary (equals to result)
        $stream->setOffset(36);
        $this->assertSame("\r", $stream->getByte()); // this is the \r after the stream keywords

        $dict = PdfDictionary::create([
            'Length' => PdfNumeric::create(12000)
        ]);

        $result = PdfStream::parse($dict, $stream);

        $this->assertSame($dict, $result->value);
        $this->assertSame($streamContent, $result->getStream());
    }

    public function testParseWithoutLength()
    {
        $in = "123 0 obj\r\n<<>>\r\nstream\r\nHello\r\nendstream\r\nendobj";

        $stream = StreamReader::createByString($in);

        // set position and prepare dictionary (equals to result)
        $stream->setOffset(23);
        $this->assertSame("\r", $stream->getByte()); // this is the \r after the stream keywords

        $dict = PdfDictionary::create([]);

        $result = PdfStream::parse($dict, $stream);

        $this->assertSame($dict, $result->value);
        $this->assertSame('Hello', $result->getStream());
    }

    public function testParseWithoutLength2()
    {
        $streamContent = str_repeat('Hello World,', 300000);
        $in = "123 0 obj\r\n<<>>\r\nstream\r\n$streamContent\r\nendstream\r\nendobj";

        $stream = StreamReader::createByString($in);

        // set position and prepare dictionary (equals to result)
        $stream->setOffset(23);
        $this->assertSame("\r", $stream->getByte()); // this is the \r after the stream keywords

        $dict = PdfDictionary::create([]);

        $result = PdfStream::parse($dict, $stream);

        $this->assertSame($dict, $result->value);
        $this->assertSame($streamContent, $result->getStream());
    }

    public function getUnfilteredStreamProvider()
    {
        $data = [];

        $pdfsPath = __DIR__ . '/../../../_files/pdfs';
        $streamsPath = __DIR__ . '/data/streams';

        $data[] = [
            $pdfsPath . '/Boombastic-Box.pdf',
            11,
            file_get_contents($streamsPath . '/Boombastic-Box.pdf/11-0-R.dump'),
        ];

        $data[] = [
            $pdfsPath . '/filters/lzw/999998.pdf',
            54,
            "q\r" .
            "660 0 0 907 0 0 cm\r" .
            "/Im1 Do\r" .
            "Q\r",
        ];

        $data[] = [
            $pdfsPath . '/filters/hex/hex.pdf',
            9,
            "q\r\n" .
            "BT\r\n" .
            "/F1 18 Tf\r\n" .
            "0 0 0 rg\r\n" .
            "200 565 Td\r\n" .
            "(Ergebnisse Audit Touran GP2 DC - CP 3 - China - Sitze) Tj\r\n" .
            "ET\r\n" .
            "Q",
        ];

        $data[] = [
            $pdfsPath . '/filters/hex/hex.pdf',
            12,
            "q\r\n" .
            "1 1 1 RG\r\n" .
            "1 1 1 rg\r\n" .
            "0 w\r\n" .
            "\r\n" .
            "20 480 800 -16 re\r\n" .
            "B\r\n" .
            "Q\r\n",
        ];

        $data[] = [
            $pdfsPath . '/filters/multiple/flate-and-hex.pdf',
            1,
            "% some content to test the filter.\n" .
            "q 1 0 0 1 0 741.89 cm 1 J 0.57 w 1.000 0.039 0.039 rg 0.00 100.00 100.00 -100.00 re f 0.00 100.00 m 100.00 0.00 l S 0.00 0.00 m 100.00 100.00 l S Q",
        ];

        return $data;
    }

    /**
     * @param $file
     * @param $objectNumber
     * @param $expectedResult
     * @dataProvider getunfilteredStreamProvider
     */
    public function testGetUnfilteredStream($file, $objectNumber, $expectedResult)
    {
        $reader = StreamReader::createByFile($file);
        $parser = new PdfParser($reader);

        $stream = PdfStream::ensure($parser->getIndirectObject($objectNumber)->value);

        $this->assertEquals($expectedResult, $stream->getUnfilteredStream());
    }
}
