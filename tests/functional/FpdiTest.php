<?php

namespace setasign\Fpdi\functional;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\PdfParser;
use setasign\Fpdi\PdfParser\StreamReader;
use setasign\Fpdi\PdfParser\Type\PdfNull;
use setasign\Fpdi\PdfParser\Type\PdfType;
use setasign\Fpdi\PdfReader\PdfReader;

class FpdiTest extends TestCase
{
    public function testReturnValueOfUseTemplate()
    {
        $pdf = new Fpdi('P', 'pt');
        $pdf->setSourceFile(__DIR__ . '/../_files/pdfs/Noisy-Tube.pdf');
        $pageId = $pdf->importPage(1);

        $pdf->AddPage();
        $size = $pdf->useTemplate($pageId, 10, 10, 100);
        $this->assertEquals([
            'width' => 100,
            'height' => 141.42851383223916,
            0 => 100,
            1 => 141.42851383223916,
            'orientation' => 'P'
        ], $size);
    }

    public function testImportedPageInTemplate()
    {
        $pdf = new Fpdi('P', 'pt');
        $pdf->setSourceFile(__DIR__ . '/../_files/pdfs/Noisy-Tube.pdf');
        $pageId = $pdf->importPage(1);

        $tplId = $pdf->beginTemplate(100, 200);
        $pdf->useTemplate($pageId, 0, 0, 100, 200);
        $pdf->endTemplate();

        $pdf->AddPage();
        $pdf->useTemplate($tplId);
        $size = $pdf->useTemplate($tplId, 20, 20);
        $this->assertEquals([
            'width' => 100,
            'height' => 200,
            0 => 100,
            1 => 200,
            'orientation' => 'P'
        ], $size);

        $pdfString = $pdf->Output('S');
//        file_put_contents('out.pdf', $pdfString);

        $parser = new PdfParser(StreamReader::createByString($pdfString));
        $pageObject = $parser->getIndirectObject(3);
        $pageContentObject = PdfType::resolve($pageObject->value->value['Contents'], $parser);
        $expectedStream = "2 J\n"
            . "0.57 w\n"
            . "q 0 J 1 w 0 j 0 G 0 g 1.0000 0 0 1.0000 0.0000 641.8900 cm /TPL1 Do Q\n"
            . "q 0 J 1 w 0 j 0 G 0 g 1.0000 0 0 1.0000 20.0000 621.8900 cm /TPL1 Do Q\n";
        $this->assertEquals($expectedStream, $pageContentObject->getUnfilteredStream());

        $pageResources = PdfType::resolve($pageObject->value->value['Resources'], $parser);
        $tplBObject = PdfType::resolve($pageResources->value['XObject']->value['TPL1'], $parser);

        $expectedStream = "2 J\n"
            . "0.57 w\n"
            . "q 0 J 1 w 0 j 0 G 0 g 0.1680 0 0 0.2376 0.0000 0.0000 cm /TPL0 Do Q\n";
        $this->assertEquals($expectedStream, $tplBObject->getUnfilteredStream());

        $tplAObject = PdfType::resolve(
            PdfType::resolve($tplBObject->value->value['Resources'], $parser)->value['XObject']->value['TPL0'],
            $parser
        );

        // let's read the original string of the imported page
        $reader = new PdfReader(new PdfParser(StreamReader::createByFile(__DIR__ . '/../_files/pdfs/Noisy-Tube.pdf')));
        $expectedStream = $reader->getPage(1)->getContentStream();
        $this->assertEquals($expectedStream, $tplAObject->getUnfilteredStream());
    }

    /**
     * @expectedException \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException
     * @expectedExceptionCode \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException::COMPRESSED_XREF
     */
    public function testBehaviourOnCompressedXref()
    {
        $pdf = new Fpdi('P', 'pt');
        $pdf->setSourceFile(__DIR__ . '/../_files/pdfs/compressed-xref.pdf');
    }

    public function testHandlingOfNoneExistingReferencedObjects()
    {
        $pdf = new Fpdi();
        $pdf->setSourceFile(__DIR__ . '/../_files/pdfs/ReferencesToInvalidObjects.pdf');
        $pdf->AddPage();
        $pdf->useTemplate($pdf->importPage(1));

        $pdfString = $pdf->Output('S');

//        var_dump($pdfString);

        $parser = new PdfParser(StreamReader::createByString($pdfString));
        $xObject = $parser->getIndirectObject(5)->value;

        $resources = PdfType::resolve($xObject->value->value['Resources'], $parser);
        $linkToNull = PdfType::resolve($resources->value['Font']->value['SETA_Test'], $parser);

        $null = PdfType::resolve($linkToNull, $parser);
        $this->assertInstanceOf(PdfNull::class, $null);
    }

    public function testSetSourceFileWithoutUsingIt()
    {
        $pdf = new Fpdi();
        $pdf->setSourceFile(__DIR__ . '/../_files/pdfs/Noisy-Tube.pdf');
        $pdfString = $pdf->Output('S');

        $parser = new PdfParser(StreamReader::createByString($pdfString));
        $trailer = $parser->getCrossReference()->getTrailer();

        $this->assertSame(7, $trailer->value['Size']->value);
    }
}