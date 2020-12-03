<?php

namespace setasign\Fpdi\functional\Tfpdf;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException;
use setasign\Fpdi\PdfParser\PdfParser;
use setasign\Fpdi\PdfParser\StreamReader;
use setasign\Fpdi\PdfParser\Type\PdfNull;
use setasign\Fpdi\PdfParser\Type\PdfType;
use setasign\Fpdi\PdfReader\PdfReader;
use setasign\Fpdi\Tfpdf\Fpdi;

class FpdiTest extends TestCase
{
    public function testReturnValueOfUseTemplate()
    {
        $pdf = new Fpdi('P', 'pt');
        $pdf->setSourceFile(__DIR__ . '/../../_files/pdfs/Noisy-Tube.pdf');
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
        $pdf->setSourceFile(__DIR__ . '/../../_files/pdfs/Noisy-Tube.pdf');
        $pageId = $pdf->importPage(1);

        $pdf->AddPage();
        // templating in TCPDF will only work as soon as a page was added!!
        $tplId = $pdf->beginTemplate(100, 200);
        $pdf->useImportedPage($pageId, 0, 0, 100, 200);
        $pdf->endTemplate();

        $pdf->useTemplate($tplId, 0, 0);
        $pdf->useTemplate($tplId, 20, 20);

        $pdfString = $pdf->Output('', 'S');
//        file_put_contents('out-tfpdf.pdf', $pdfString);

        $parser = new PdfParser(StreamReader::createByString($pdfString));
        $pageObject = $parser->getIndirectObject(3);

        $pageContentObject = PdfType::resolve($pageObject->value->value['Contents'], $parser);
        $expectedStream = "2 J\n"
            . "0.57 w\n"
            . "q 0 J 1 w 0 j 0 G 0 g 1.0000 0 0 1.0000 0.0000 641.8900 cm /TPL1 Do Q\n"
            . "q 0 J 1 w 0 j 0 G 0 g 1.0000 0 0 1.0000 20.0000 621.8900 cm /TPL1 Do Q\n";
        $this->assertStringStartsWith($expectedStream, $pageContentObject->getUnfilteredStream());

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
        $reader = new PdfReader(new PdfParser(StreamReader::createByFile(__DIR__ . '/../../_files/pdfs/Noisy-Tube.pdf')));
        $expectedStream = $reader->getPage(1)->getContentStream();
        $this->assertEquals($expectedStream, $tplAObject->getUnfilteredStream());
    }

    public function testBehaviourOnCompressedXref()
    {
        $pdf = new Fpdi('P', 'pt');
        $this->expectException(CrossReferenceException::class);
        $this->expectExceptionCode(CrossReferenceException::COMPRESSED_XREF);
        $pdf->setSourceFile(__DIR__ . '/../../_files/pdfs/compressed-xref.pdf');
    }

    public function testHandlingOfNoneExistingReferencedObjects()
    {
        $pdf = new Fpdi('P', 'pt');
        $pdf->setSourceFile(__DIR__ . '/../../_files/pdfs/ReferencesToInvalidObjects.pdf');
        $pdf->AddPage();
        $pdf->useTemplate($pdf->importPage(1));

        $pdfString = $pdf->Output('doc.pdf', 'S');

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
        $pdf = new Fpdi('P', 'pt');
        $pdf->setSourceFile(__DIR__ . '/../../_files/pdfs/Noisy-Tube.pdf');
        $pdfString = $pdf->Output('doc.pdf', 'S');
//file_put_contents('out-tfpdf.pdf', $pdfString);

        $parser = new PdfParser(StreamReader::createByString($pdfString));
        $trailer = $parser->getCrossReference()->getTrailer();

        $this->assertSame(7, $trailer->value['Size']->value);
    }

    public function testStreamHandleIsOpen()
    {
        $tmpName = tempnam(sys_get_temp_dir(), 'fpdi-test');
        copy(__DIR__ . '/../../_files/pdfs/Noisy-Tube.pdf', $tmpName);
        $pdf = new Fpdi();
        $pdf->setSourceFile($tmpName);

        try {
            unlink($tmpName);
            $this->markTestSkipped('Stream was not locked on this OS.');
        } catch (\PHPUnit_Framework_Error_Warning $e) {
            $pdf->cleanUp();
        }

        $this->assertTrue(unlink($tmpName));
    }

    public function testReleaseOfStreamHandleOnUnset()
    {
        $tmpName = tempnam(sys_get_temp_dir(), 'fpdi-test');
        copy(__DIR__ . '/../../_files/pdfs/Noisy-Tube.pdf', $tmpName);
        $pdf = new Fpdi();
        $pdf->setSourceFile($tmpName);
        $tpl = $pdf->importPage(1);
        $pdf->AddPage();
        $pdf->useTemplate($tpl);
        $a = $pdf->Output('doc.pdf', 'S');
        $b = $pdf->Output('doc.pdf', 'S');
        unset($pdf);

        $this->assertSame($a, $b);

        $this->assertTrue(unlink($tmpName));
    }
}