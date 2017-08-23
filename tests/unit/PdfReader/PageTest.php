<?php

namespace setasign\Fpdi\unit\PdfReader;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\PdfParser\PdfParser;
use setasign\Fpdi\PdfParser\Type\PdfArray;
use setasign\Fpdi\PdfParser\Type\PdfDictionary;
use setasign\Fpdi\PdfParser\Type\PdfIndirectObject;
use setasign\Fpdi\PdfParser\Type\PdfIndirectObjectReference;
use setasign\Fpdi\PdfParser\Type\PdfName;
use setasign\Fpdi\PdfParser\Type\PdfNumeric;
use setasign\Fpdi\PdfParser\Type\PdfStream;
use setasign\Fpdi\PdfReader\DataStructure\Rectangle;
use setasign\Fpdi\PdfReader\Page;
use setasign\Fpdi\PdfReader\PageBoundaries;

class PageTest extends TestCase
{

    public function testGetAttribute()
    {
        $page = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPageDictionary'])
            ->getMock();

        $dict = PdfDictionary::create([
            'Type' => PdfName::create('Page')
        ]);

        $page->expects($this->any())
            ->method('getPageDictionary')
            ->willReturn($dict);

        $this->assertEquals(PdfName::create('Page'), $page->getAttribute('Type'));
        $this->assertNull($page->getAttribute('Anything'));
    }

    public function testGetAttributeWithInheritance()
    {
        // should be resolved as object 2
        $pages3 = PdfDictionary::create([
            'Type' => PdfName::create('Pages'),
            'Rotate' => PdfNumeric::create(90), // will be overwritten
            'MediaBox' => $mediaBox = PdfArray::create([
                PdfNumeric::create(0),
                PdfNumeric::create(0),
                PdfNumeric::create(100),
                PdfNumeric::create(200),
            ]),
            'CropBox' => $cropBox = PdfArray::create([
                PdfNumeric::create(10),
                PdfNumeric::create(10),
                PdfNumeric::create(90),
                PdfNumeric::create(190),
            ]),
        ]);

        // should be resolved as object 2
        $pages2 = PdfDictionary::create([
            'Type' => PdfName::create('Pages'),
            'Rotate' => PdfNumeric::create(180),
            'Parent' => PdfIndirectObjectReference::create(3, 0)
        ]);

        $dict = PdfDictionary::create([
            'Type' => PdfName::create('Page'),
            'Parent' => PdfIndirectObjectReference::create(2, 0)
        ]);

        $parser = $this->getMockBuilder(PdfParser::class)
            ->setMethods(['getIndirectObject'])
            ->disableOriginalConstructor()
            ->getMock();

        $parser->expects($this->exactly(2))
            ->method('getIndirectObject')
            ->withConsecutive([2], [3])
            ->willReturnOnConsecutiveCalls($pages2, $pages3);

        $object = PdfIndirectObject::create(1, 0, $dict);

        $page = $this->getMockBuilder(Page::class)
            ->setMethods(['getPageDictionary'])
            ->setConstructorArgs([$object, $parser])
            ->getMock();

        $page->expects($this->any())
            ->method('getPageDictionary')
            ->willReturn($dict);

        $this->assertEquals(PdfName::create('Page'), $page->getAttribute('Type'));
        $this->assertEquals(PdfNumeric::create(180), $page->getAttribute('Rotate'));

        $this->assertEquals($mediaBox, $page->getAttribute('MediaBox'));
        $this->assertEquals($cropBox, $page->getAttribute('CropBox'));
        $this->assertNull($page->getAttribute('Resources')); // ensures that inherited attributes are only resolved once
    }

    private function getPageMock($dict, &$parser = null)
    {
        $parser = $this->getMockBuilder(PdfParser::class)
            ->setMethods(['getIndirectObject'])
            ->disableOriginalConstructor()
            ->getMock();

        $object = PdfIndirectObject::create(1, 0, $dict);

        $page = $this->getMockBuilder(Page::class)
            ->setConstructorArgs([$object, $parser])
            ->setMethods(['getPageDictionary'])
            ->getMock();

        $page->expects($this->any())
            ->method('getPageDictionary')
            ->willReturn($dict);

        return $page;
    }

    public function testGetRotationDefaultValue()
    {
        $dict = PdfDictionary::create([]);
        $page = $this->getPageMock($dict);

        $this->assertEquals(0, $page->getRotation());
    }

    public function testGetRotation()
    {
        $dict = PdfDictionary::create([
            'Rotate' => PdfNumeric::create(90)
        ]);
        $page = $this->getPageMock($dict);

        $this->assertEquals(90, $page->getRotation());
    }

    public function testGetRotationReferencedValue()
    {
        $value = PdfNumeric::create(-90);
        $dict = PdfDictionary::create([
            'Rotate' => PdfIndirectObjectReference::create(2, 0)
        ]);
        $page = $this->getPageMock($dict, $parser);
        $parser->expects($this->once())
            ->method('getIndirectObject')
            ->with(2)
            ->willReturn($value);

        // internally -90 is changed to 270
        $this->assertEquals(270, $page->getRotation());
    }

    public function testGetBoundary()
    {
        $dict = PdfDictionary::create([
            'Type' => PdfName::create('Page'),
            'MediaBox' => $mediaBox = PdfArray::create([
                PdfNumeric::create(0),
                PdfNumeric::create(0),
                PdfNumeric::create(100),
                PdfNumeric::create(200),
            ]),
            'CropBox' => $cropBox = PdfArray::create([
                PdfNumeric::create(10),
                PdfNumeric::create(10),
                PdfNumeric::create(90),
                PdfNumeric::create(190),
            ]),
        ]);

        $page = $this->getPageMock($dict, $parser);

        $cropBox = Rectangle::byPdfArray($cropBox, $parser);
        $this->assertEquals($cropBox, $page->getBoundary());

        $mediaBox = Rectangle::byPdfArray($mediaBox, $parser);
        $this->assertEquals($mediaBox, $page->getBoundary(PageBoundaries::MEDIA_BOX));

        $this->assertFalse($page->getBoundary(PageBoundaries::ART_BOX, false));
        $this->assertEquals($cropBox, $page->getBoundary(PageBoundaries::ART_BOX));
        $this->assertEquals($cropBox, $page->getBoundary(PageBoundaries::TRIM_BOX));
        $this->assertEquals($cropBox, $page->getBoundary(PageBoundaries::BLEED_BOX));
    }

    public function testGetContentStreamWithASingleStream()
    {
        // object number 1
        $content = 'A simple stream';
        $stream = PdfStream::create(PdfDictionary::create([
            'Length' => PdfNumeric::create(strlen($content))
        ]), $content);
        $object = PdfIndirectObject::create(1, 0, $stream);

        $dict = PdfDictionary::create([
            'Type' => PdfName::create('Page'),
            'Contents' => PdfIndirectObjectReference::create(1, 0)
        ]);

        $page = $this->getPageMock($dict, $parser);
        $parser->expects($this->once())
            ->method('getIndirectObject')
            ->with(1)
            ->willReturn($object);

        $this->assertSame($content, $page->getContentStream());
    }

    public function testGetContentStreamWithSeveralStreams()
    {
        // object number 1
        $content1 = 'First stream';
        $stream1 = PdfStream::create(PdfDictionary::create([
            'Length' => PdfNumeric::create(strlen($content1))
        ]), $content1);
        $object1 = PdfIndirectObject::create(1, 0, $stream1);

        $content2 = 'Second stream';
        $stream2= PdfStream::create(PdfDictionary::create([
            'Length' => PdfNumeric::create(strlen($content2))
        ]), $content2);
        $object2 = PdfIndirectObject::create(1, 0, $stream2);

        $dict = PdfDictionary::create([
            'Type' => PdfName::create('Page'),
            'Contents' => PdfArray::create([
                PdfIndirectObjectReference::create(1, 0),
                PdfIndirectObjectReference::create(2, 0)
            ])
        ]);

        $page = $this->getPageMock($dict, $parser);
        $parser->expects($this->exactly(2))
            ->method('getIndirectObject')
            ->withConsecutive([1], [2])
            ->willReturnOnConsecutiveCalls($object1, $object2);

        $this->assertSame($content1 . "\n" . $content2, $page->getContentStream());
    }
}