<?php

namespace setasign\Fpdi\functional\PdfParser\Type;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\PdfParser\CrossReference\CrossReference;
use setasign\Fpdi\PdfParser\PdfParser;
use setasign\Fpdi\PdfParser\StreamReader;
use setasign\Fpdi\PdfParser\Tokenizer;
use setasign\Fpdi\PdfParser\Type\PdfBoolean;
use setasign\Fpdi\PdfParser\Type\PdfDictionary;
use setasign\Fpdi\PdfParser\Type\PdfIndirectObject;
use setasign\Fpdi\PdfParser\Type\PdfIndirectObjectReference;
use setasign\Fpdi\PdfParser\Type\PdfName;
use setasign\Fpdi\PdfParser\Type\PdfNull;
use setasign\Fpdi\PdfParser\Type\PdfNumeric;
use setasign\Fpdi\PdfParser\Type\PdfStream;
use setasign\Fpdi\PdfParser\Type\PdfToken;
use setasign\Fpdi\PdfParser\Type\PdfType;

class PdfStreamTest extends TestCase
{
    public function testParse()
    {
        $in = "123 0 obj\n<</Length 5>>\nstream\nHello\nendstream\nendobj";

        $stream = StreamReader::createByString($in);

        // set position and prepare dictionary (equals to result)
        $stream->setOffset(30);
        $this->assertSame("\n", $stream->getByte()); // this is the \n after the stream keyword

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
        $this->assertSame("\r", $stream->getByte()); // this is the \r after the stream keyword

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
        $this->assertSame("\r", $stream->getByte()); // this is the \r after the stream keyword

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
        $this->assertSame("\r", $stream->getByte()); // this is the \r after the stream keyword

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
        $this->assertSame("\r", $stream->getByte()); // this is the \r after the stream keyword

        $dict = PdfDictionary::create([]);

        $result = PdfStream::parse($dict, $stream);

        $this->assertSame($dict, $result->value);
        $this->assertSame($streamContent, $result->getStream());
    }

    public function testParseWithoutLength3()
    {
        // validate EOL behavior. The eol marker should be:
        //   CARRIAGE RETURN and a LINE FEED or just a LINE FEED, and not by a CARRIAGE RETURN alone
        // So the \r is part of the stream.
        $streamContent = str_repeat('Hello World,', 300000) . "\r";
        $in = "123 0 obj\n<<>>\nstream\n$streamContent" . "endstream\nendobj";

        $stream = StreamReader::createByString($in);

        // set position and prepare dictionary (equals to result)
        $stream->setOffset(21);
        $this->assertSame("\n", $stream->getByte()); // this is the \n after the stream keyword

        $dict = PdfDictionary::create([]);

        $result = PdfStream::parse($dict, $stream);

        $this->assertSame($dict, $result->value);
        $this->assertSame($streamContent, $result->getStream());
    }

    public function testParseWithLengthInIndirectObject()
    {
        /**
         * This tests the indirect object handling and also the behavior if the eol marker \n is part of the stream
         * while an eol marker is missing completely (found in real life documents).
         */
        $streamContent = "a simple text with a line end on its end\n";
        $in = "stream\n"
            . $streamContent
            . "endstream\n"
            . "endobj";

        $stream = StreamReader::createByString($in);

        // set position and prepare dictionary (equals to result)
        $stream->setOffset(6);
        $this->assertSame("\n", $stream->getByte()); // this is the \n after the stream keyword

        $dict = PdfDictionary::create(['Length' => PdfIndirectObjectReference::create(1, 0)]);

        $xref = $this->getMockBuilder(CrossReference::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIndirectObject'])
            ->getMock();

        $tokenizer = $this->getMockBuilder(Tokenizer::class)
            ->disableOriginalConstructor()
            ->setMethods(['clearStack'])
            ->getMock();

        $tokenizer->expects($this->exactly(1))
            ->method('clearStack');

        $parser = $this->getMockBuilder(PdfParser::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCrossReference', 'readValue', 'getTokenizer'])
            ->getMock();

        $parser->expects($this->exactly(1))
            ->method('getCrossReference')
            ->willReturn($xref);

        $parser->expects($this->exactly(1))
            ->method('readValue')
            ->willReturn(PdfToken::create('endstream'));

        $parser->expects($this->exactly(1))
            ->method('getTokenizer')
            ->willReturn($tokenizer);

        $expectedResult = PdfIndirectObject::create(1, 0, PdfNumeric::create(strlen($streamContent)));
        $xref->expects($this->exactly(1))
            ->method('getIndirectObject')
            ->with(1)
            ->willReturn($expectedResult);

        $result = PdfStream::parse($dict, $stream, $parser);

        $this->assertSame($dict, $result->value);
        $this->assertSame($streamContent, $result->getStream());
    }

    public function testParseWithInvalidLength()
    {
        $streamContent = "A simple text with some dummy text in it.";
        $in = "stream\n"
            . $streamContent . "\n"
            . "endstream\n"
            . "endobj";

        $stream = StreamReader::createByString($in);

        $stream->setOffset(6);
        $this->assertSame("\n", $stream->getByte());

        // Length of 5 is invalid
        $dict = PdfDictionary::create(['Length' => PdfNumeric::create(5)]);

        $tokenizer = $this->getMockBuilder(Tokenizer::class)
            ->disableOriginalConstructor()
            ->setMethods(['clearStack'])
            ->getMock();

        $tokenizer->expects($this->exactly(1))
            ->method('clearStack');

        $parser = $this->getMockBuilder(PdfParser::class)
            ->disableOriginalConstructor()
            ->setMethods(['readValue', 'getTokenizer'])
            ->getMock();

        $parser->expects($this->exactly(1))
            ->method('readValue')
            ->willReturn(PdfToken::create('ple'));

        $parser->expects($this->exactly(1))
            ->method('getTokenizer')
            ->willReturn($tokenizer);

        $result = PdfStream::parse($dict, $stream, $parser);

        $this->assertSame($dict, $result->value);
        $this->assertSame($streamContent, $result->getStream());
    }

    public function testParseWithZeroLength()
    {
        $streamContent = "A simple text with some dummy text in it.";
        $in = "stream\n"
            . $streamContent . "\n"
            . "endstream\n"
            . "endobj";

        $stream = StreamReader::createByString($in);

        $stream->setOffset(6);
        $this->assertSame("\n", $stream->getByte());

        // Length of 0 - extract the stream manually
        $dict = PdfDictionary::create(['Length' => PdfNumeric::create(0)]);

        $result = PdfStream::parse($dict, $stream);

        $this->assertSame($dict, $result->value);
        $this->assertSame($streamContent, $result->getStream());
    }

    public function testParseWithZeroLengthButAMixOfLinefeedAndCarrigeReturn()
    {
        $in = "stream\r"
            . "\n"
            . "\rendstream\r" // the inital \r in this line will be the content because of the \r\n | \r | \n logic to match the start of a content stream.
            . "endobj";

        $stream = StreamReader::createByString($in);

        $stream->setOffset(6);
        $this->assertSame("\r", $stream->getByte());

        // Length of 0 - extract the stream manually
        $dict = PdfDictionary::create(['Length' => PdfNumeric::create(0), 'Filter' => PdfName::create('FlateDecode')]);

        $result = PdfStream::parse($dict, $stream);

        $this->assertSame($dict, $result->value);
        $this->assertSame('', $result->getStream());
    }

    public function testParseWithEmptyStream()
    {
        $streamContent = "";
        $in = "stream\n"
            . $streamContent . "\n"
            . "endstream\n"
            . "endobj";

        $stream = StreamReader::createByString($in);

        $stream->setOffset(6);
        $this->assertSame("\n", $stream->getByte());

        // Length of 0 - correct
        $dict = PdfDictionary::create(['Length' => PdfNumeric::create(0)]);

        $result = PdfStream::parse($dict, $stream);

        $this->assertSame($dict, $result->value);
        $this->assertSame($streamContent, $result->getStream());
    }

    public function testParseWithTokenStack()
    {
        /**
         * This test ensures that the Length value was used and that the stream is not read by the extractStream()
         * method. Also the stream itself is something special. It ends with a \n which is part of the stream.
         * So resolving this manually would result in a kind of faulty string.
         */
        $streamContent = "A simple text with some dummy text in it.\n";
        $pdf = "%PDF-1.7\n" .
            "%\xE2\xE3\xCF\xD3\n" .
            "1 0 obj\n" .
            "<</Length " . strlen($streamContent) . ">>\n"
            . "stream\n"
            . $streamContent // without additional \n
            . "endstream\n"
            . "endobj";
        $offset = strlen($pdf);
        $pdf .=
            "xref\n" .
            "0 2\r\n" .
            "0000000000 65535 f\r\n" .
            "0000000015 00000 n\r\n" .
            "trailer\n" .
            "<</Size 2 /Root 1 0 R>>\n" .
            "startxref\n" .
            "$offset\n" .
            "%%EOF";

        $stream = StreamReader::createByString($pdf);
        $parser = new PdfParser($stream);
        $xref = new CrossReference($parser);
        $stream = PdfType::resolve($xref->getIndirectObject(1), $parser);

        // Simulate filling of token stack
        $token = $parser->getTokenizer()->getNextToken();
        $parser->getTokenizer()->pushStack($token);

        $this->assertSame($streamContent, $stream->getStream());
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

    public function testParseWithCryptFilter()
    {
        $in = "123 0 obj\n<</Filter /Crypt /Length 5>>\nstream\nHello\nendstream\nendobj";

        $stream = StreamReader::createByString($in);

        // set position and prepare dictionary (equals to result)
        $stream->setOffset(45);
        $this->assertSame("\n", $stream->getByte()); // this is the \n after the stream keyword

        $dict = PdfDictionary::create([
            'Filter' => PdfName::create('Crypt'),
            'Length' => PdfNumeric::create(5)
        ]);

        $result = PdfStream::parse($dict, $stream);

        $this->assertSame($dict, $result->value);
        $this->assertSame('Hello', $result->getUnfilteredStream());
    }
}
