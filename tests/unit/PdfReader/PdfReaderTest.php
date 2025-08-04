<?php

declare(strict_types=1);

namespace setasign\Fpdi\unit\PdfReader;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\PdfParser\PdfParser;
use setasign\Fpdi\PdfParser\Type\PdfArray;
use setasign\Fpdi\PdfParser\Type\PdfDictionary;
use setasign\Fpdi\PdfParser\Type\PdfIndirectObject;
use setasign\Fpdi\PdfParser\Type\PdfIndirectObjectReference;
use setasign\Fpdi\PdfParser\Type\PdfName;
use setasign\Fpdi\PdfParser\Type\PdfNumeric;
use setasign\Fpdi\PdfReader\PdfReader;
use setasign\Fpdi\PdfReader\PdfReaderException;

class PdfReaderTest extends TestCase
{
    public function testHandlingOfRecursivePageTreeStructure()
    {
        $parser = (
            $this->getMockBuilder(PdfParser::class)
            ->setMethods(['getCatalog', 'getIndirectObject'])
            ->disableOriginalConstructor()
            ->getMock()
        );

        $pages1 = PdfIndirectObject::create(2, 0, PdfDictionary::create([
            'Type' => PdfName::create('Pages'),
            'Count' => PdfNumeric::create(1),
            'Kids' => PdfArray::create([PdfIndirectObjectReference::create(3, 0)])
        ]));
        $pages2 = PdfIndirectObject::create(3, 0, PdfDictionary::create([
            'Type' => PdfName::create('Pages'),
            'Parent' => PdfIndirectObjectReference::create(2, 0),
            'Count' => PdfNumeric::create(1),
            'Kids' => PdfArray::create([PdfIndirectObjectReference::create(2, 0)])
        ]));
        $parser->method('getIndirectObject')->willReturnMap([
            [2, false, $pages1],
            [3, false, $pages2],
        ]);

        $parser->method('getCatalog')->willReturn(PdfDictionary::create([
            'Pages' => PdfDictionary::create([
                'Count' => PdfNumeric::create(1),
                'Kids' => PdfArray::create([PdfIndirectObjectReference::create(2, 0)])
            ])
        ]));

        $pdfReader = new PdfReader($parser);
        $this->assertEquals(1, $pdfReader->getPageCount());

        $this->expectException(PdfReaderException::class);
        $this->expectExceptionMessage('Recursive pages dictionary detected.');
        $pdfReader->getPage(1);
    }

    public function testHandlingOfRecursivePageTreeStructureWhenFullTreeIsRead()
    {
        $parser = (
            $this->getMockBuilder(PdfParser::class)
            ->setMethods(['getCatalog', 'getIndirectObject'])
            ->disableOriginalConstructor()
            ->getMock()
        );

        $pages1 = PdfIndirectObject::create(2, 0, PdfDictionary::create([
            'Type' => PdfName::create('Pages'),
            'Count' => PdfNumeric::create(3),
            'Kids' => PdfArray::create([PdfIndirectObjectReference::create(3, 0)])
        ]));
        $pages2 = PdfIndirectObject::create(3, 0, PdfDictionary::create([
            'Type' => PdfName::create('Pages'),
            'Parent' => PdfIndirectObjectReference::create(2, 0),
            'Count' => PdfNumeric::create(2),
            'Kids' => PdfArray::create([PdfIndirectObjectReference::create(2, 0)])
        ]));
        $parser->method('getIndirectObject')->willReturnMap([
            [2, false, $pages1],
            [3, false, $pages2],
        ]);

        $parser->method('getCatalog')->willReturn(PdfDictionary::create([
            'Pages' => PdfDictionary::create([
                'Count' => PdfNumeric::create(5),
                'Kids' => PdfArray::create([PdfIndirectObjectReference::create(2, 0)])
            ])
        ]));

        $pdfReader = new PdfReader($parser);
        $this->assertEquals(5, $pdfReader->getPageCount());

        $this->expectException(PdfReaderException::class);
        $this->expectExceptionMessage('Recursive pages dictionary detected.');
        $pdfReader->getPage(1);
    }
}
