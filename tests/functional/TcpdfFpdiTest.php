<?php

namespace setasign\Fpdi\functional;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\PdfParser;
use setasign\Fpdi\PdfParser\StreamReader;
use setasign\Fpdi\PdfParser\Type\PdfNull;
use setasign\Fpdi\PdfParser\Type\PdfType;
use setasign\Fpdi\PdfReader\PdfReader;
use setasign\Fpdi\TcpdfFpdi;

class TcpdfFpdiTest extends TestCase
{
    public function testReturnValueOfUseTemplate()
    {
        $pdf = new TcpdfFpdi('P', 'pt');
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
        $pdf = new TcpdfFpdi('P', 'pt');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->setSourceFile(__DIR__ . '/../_files/pdfs/Noisy-Tube.pdf');
        $pageId = $pdf->importPage(1);

        $pdf->AddPage();
        // templating in TCPDF will only work as soon as a page was added!!
        $tplId = $pdf->startTemplate(100, 200);
        $pdf->useImportedPage($pageId, 0, 0, 100, 200);
        $pdf->endTemplate();

        $pdf->printTemplate($tplId, 0, 0);
        $pdf->printTemplate($tplId, 20, 20);

        $pdfString = $pdf->Output('', 'S');
//        file_put_contents('out-tcpdf.pdf', $pdfString);

        $parser = new PdfParser(StreamReader::createByString($pdfString));
        $pageObject = $parser->getIndirectObject(6);

        $pageContentObject = PdfType::resolve($pageObject->value->value['Contents'], $parser);
        $expectedStream = "0.570000 w 0 J 0 j [] 0 d 0 G 0 g\n"
            . "BT /F1 12.000000 Tf ET\n"
            . "0.570000 w 0 J 0 j [] 0 d 0 G 0 g\n"
            . "BT /F1 12.000000 Tf ET\n"
            . "0.570000 w 0 J 0 j [] 0 d 0 G 0 g\n"
            . "BT /F1 12.000000 Tf ET\n"
            . "q\n"
            . "1.000000 0.000000 0.000000 1.000000 0.000000 641.890000 cm\n"
            . "/XT4 Do\n"
            . "Q\n"
            . "q\n"
            . "1.000000 0.000000 0.000000 1.000000 20.000000 621.890000 cm\n"
            . "/XT4 Do\n"
            . "Q\n";
        $this->assertStringStartsWith($expectedStream, $pageContentObject->getUnfilteredStream());

        $pageResources = PdfType::resolve($pageObject->value->value['Resources'], $parser);
        $tplBObject = PdfType::resolve($pageResources->value['XObject']->value['XT4'], $parser);

        $expectedStream = "q 0 J 1 w 0 j 0 G 0 g 0.1680 0 0 0.2376 0.0000 0.0000 cm /TPL0 Do Q";
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
        $pdf = new TcpdfFpdi('P', 'pt');
        $pdf->setSourceFile(__DIR__ . '/../_files/pdfs/compressed-xref.pdf');
    }

    public function testHandlingOfNoneExistingReferencedObjects()
    {
        $pdf = new TcpdfFpdi();
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->setSourceFile(__DIR__ . '/../_files/pdfs/ReferencesToInvalidObjects.pdf');
        $pdf->AddPage();
        $pdf->useTemplate($pdf->importPage(1));

        $pdfString = $pdf->Output('doc.pdf', 'S');

//        var_dump($pdfString);

        $parser = new PdfParser(StreamReader::createByString($pdfString));
        $xObject = $parser->getIndirectObject(7)->value;

        $resources = PdfType::resolve($xObject->value->value['Resources'], $parser);
        $linkToNull = PdfType::resolve($resources->value['Font']->value['SETA_Test'], $parser);

        $null = PdfType::resolve($linkToNull, $parser);
        $this->assertInstanceOf(PdfNull::class, $null);
    }

    public function testSetSourceFileWithoutUsingIt()
    {
        $pdf = new TcpdfFpdi();
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->setSourceFile(__DIR__ . '/../_files/pdfs/Noisy-Tube.pdf');
        $pdfString = $pdf->Output('doc.pdf', 'S');
//file_put_contents('out-tcpdf.pdf', $pdfString);

        $parser = new PdfParser(StreamReader::createByString($pdfString));
        $trailer = $parser->getCrossReference()->getTrailer();

        $this->assertSame(10, $trailer->value['Size']->value);
    }
}