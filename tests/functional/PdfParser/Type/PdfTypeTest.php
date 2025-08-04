<?php

declare(strict_types=1);

namespace PdfParser\Type;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\PdfParser\PdfParser;
use setasign\Fpdi\PdfParser\PdfParserException;
use setasign\Fpdi\PdfParser\Type\PdfIndirectObject;
use setasign\Fpdi\PdfParser\Type\PdfIndirectObjectReference;
use setasign\Fpdi\PdfParser\Type\PdfType;

class PdfTypeTest extends TestCase
{
    public function testResolveWithRecursiveReferences()
    {
        $parser = (
            $this->getMockBuilder(PdfParser::class)
            ->setMethods(['getCatalog', 'getIndirectObject'])
            ->disableOriginalConstructor()
            ->getMock()
        );

        $object1 = PdfIndirectObject::create(1, 0, PdfIndirectObjectReference::create(2, 0));
        $object2 = PdfIndirectObject::create(2, 0, PdfIndirectObjectReference::create(1, 0));
        $parser->method('getIndirectObject')->willReturnMap([
            [1, false, $object1],
            [2, false, $object2],
        ]);

        $this->expectException(PdfParserException::class);
        $this->expectExceptionMessage('Indirect reference recursion detected (1).');
        PdfType::resolve($object1, $parser);
    }
}
