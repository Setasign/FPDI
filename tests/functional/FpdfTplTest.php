<?php

namespace setasign\Fpdi\functional;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\FpdfTpl;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\PdfParser;
use setasign\Fpdi\PdfParser\StreamReader;
use setasign\Fpdi\PdfParser\Type\PdfStream;
use setasign\Fpdi\PdfParser\Type\PdfType;
use setasign\Fpdi\PdfReader\PdfReader;

class FpdfTplTest extends TestCase
{
    public function testGetTemplateSizeWithInvalidArgument()
    {
        $pdf = new FpdfTpl();
        $this->assertFalse($pdf->getTemplateSize('anything'));
    }

    public function testGetTemplateSize()
    {
        $pdf = new FpdfTpl();
        $tplId = $pdf->beginTemplate(100, 200);
        $size = $pdf->getTemplateSize($tplId);

        $this->assertEquals([
            'width' => 100,
            'height' => 200,
            0 => 100,
            1 => 200,
            'orientation' => 'P'
        ], $size);
    }

    public function testGetTemplateSizeResizedByWidth()
    {
        $pdf = new FpdfTpl();
        $tplId = $pdf->beginTemplate(100, 200);
        $size = $pdf->getTemplateSize($tplId, 200);

        $this->assertEquals([
            'width' => 200,
            'height' => 400,
            0 => 200,
            1 => 400,
            'orientation' => 'P'
        ], $size);
    }

    public function testGetTemplateSizeResizedByHeight()
    {
        $pdf = new FpdfTpl();
        $tplId = $pdf->beginTemplate(100, 200);
        $size = $pdf->getTemplateSize($tplId, null, 100);

        $this->assertEquals([
            'width' => 50,
            'height' => 100,
            0 => 50,
            1 => 100,
            'orientation' => 'P'
        ], $size);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetTemplateSizeWithZeroWidth()
    {
        $pdf = new FpdfTpl();
        $tplId = $pdf->beginTemplate(100, 200);
        $pdf->getTemplateSize($tplId, 0);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetTemplateSizeWithZeroHeight()
    {
        $pdf = new FpdfTpl();
        $tplId = $pdf->beginTemplate(100, 200);
        $pdf->getTemplateSize($tplId, null, 0);
    }

    public function testBeginAndEndTemplate()
    {
        $pdf = new Fpdi();
        $tplIdA = $pdf->beginTemplate();
        $tplIdB = $pdf->endTemplate();

        $this->assertEquals($tplIdA, $tplIdB);

        $this->assertFalse($pdf->endTemplate());
    }

    public function testTemplateInResources()
    {
        $pdf = new FpdfTpl();
        // create 2 templates
        $pdf->beginTemplate();
        $pdf->endTemplate();
        $pdf->beginTemplate();
        $pdf->endTemplate();
        $pdfString = $pdf->Output('S');

        $parser = new PdfParser(StreamReader::createByString($pdfString));
        $object = $parser->getIndirectObject(2);

        // reference to object id 5
        $this->assertEquals(5, $object->value->value['XObject']->value['TPL0']->value);
        // the other one to 6
        $this->assertEquals(6, $object->value->value['XObject']->value['TPL1']->value);
    }

    public function testTemplateWithSimpleRect()
    {
        $pdf = new FpdfTpl();
        // create 2 templates
        $id = $pdf->beginTemplate();
            $pdf->SetFillColor(255, 100, 100);
            $pdf->SetDrawColor(0, 255, 100);
            $pdf->Rect(10, 10, 90, 90, 'FD');
        $pdf->endTemplate();
        $pdf->AddPage();
        $pdf->useTemplate($id);
        $pdfString = $pdf->Output('S');

//        file_put_contents('out.pdf', $pdfString);

        $parser = new PdfParser(StreamReader::createByString($pdfString));
        $object = $parser->getIndirectObject(2);

        // reference to object id 5
        $objectId = $object->value->value['XObject']->value['TPL0']->value;
        $this->assertEquals(5, $objectId);

        $object = $parser->getIndirectObject($objectId);
        $expectedStream = "2 J\n"
            . "0.57 w\n"
            . "1.000 0.392 0.392 rg\n"
            . "0.000 1.000 0.392 RG\n"
            . "28.35 813.54 255.12 -255.12 re B\n";
        $this->assertEquals($expectedStream, $object->value->getUnfilteredStream());

        $reader = new PdfReader($parser);
        $expectedStream = "2 J\n"
            . "0.57 w\n"
            . "0.000 1.000 0.392 RG\n"
            . "1.000 0.392 0.392 rg\n"
            . "q 0 J 1 w 0 j 0 G 0 g 1.0000 0 0 1.0000 0.0000 0.0000 cm /TPL0 Do Q\n";

        $this->assertEquals($expectedStream, $reader->getPage(1)->getContentStream());
    }

    public function testTemplateUsedSeveralTimes()
    {
        $pdf = new FpdfTpl();
        // create 2 templates
        $id = $pdf->beginTemplate();
        $pdf->SetFillColor(255, 100, 100);
        $pdf->SetDrawColor(0, 255, 100);
        $pdf->Rect(10, 10, 90, 40, 'FD');
        $pdf->endTemplate();
        $pdf->AddPage();
        $pdf->SetDrawColor(0, 0, 250);

        $size = $pdf->useTemplate($id, 10, 10, 100);
        $pdf->Rect(0, 0, $size['width'], $size['height']);
        $size = $pdf->useTemplate($id, 50, 50, 120);
        $pdf->Rect(50, 50, $size['width'], $size['height']);

        $size = $pdf->useTemplate($id, 90, 90, 80);
        $pdf->Rect(90, 90, $size['width'], $size['height']);

        $pdfString = $pdf->Output('S');

//        file_put_contents('out.pdf', $pdfString);

        $parser = new PdfParser(StreamReader::createByString($pdfString));
        $object = $parser->getIndirectObject(2);

        // reference to object id 5
        $objectId = $object->value->value['XObject']->value['TPL0']->value;
        $this->assertEquals(5, $objectId);

        $object = $parser->getIndirectObject($objectId);
        $expectedStream = "2 J\n"
            . "0.57 w\n"
            . "1.000 0.392 0.392 rg\n"
            . "0.000 1.000 0.392 RG\n"
            . "28.35 813.54 255.12 -113.39 re B\n";
        $this->assertEquals($expectedStream, $object->value->getUnfilteredStream());

        $reader = new PdfReader($parser);
        $expectedStream = "2 J\n"
            . "0.57 w\n"
            . "0.000 1.000 0.392 RG\n"
            . "1.000 0.392 0.392 rg\n"
            . "0.000 0.000 0.980 RG\n"
            . "q 0 J 1 w 0 j 0 G 0 g 0.4762 0 0 0.4762 28.3465 412.6465 cm /TPL0 Do Q\n"
            . "0.00 841.89 283.46 -400.90 re S\n"
            . "q 0 J 1 w 0 j 0 G 0 g 0.5714 0 0 0.5714 141.7323 219.0813 cm /TPL0 Do Q\n"
            . "141.73 700.16 340.16 -481.08 re S\n"
            . "q 0 J 1 w 0 j 0 G 0 g 0.3809 0 0 0.3809 255.1181 266.0543 cm /TPL0 Do Q\n"
            . "255.12 586.77 226.77 -320.72 re S\n";

        $this->assertEquals($expectedStream, $reader->getPage(1)->getContentStream());
    }

    public function testImageInTemplate()
    {
        $pdf = new FpdfTpl();
        $pdf->beginTemplate(100, 200);
        $pdf->Image(__DIR__ .'/../_files/images/png-8.png', 0, 0, 100, 200);
        $tplId = $pdf->endTemplate();

        $pdf->AddPage();
        $pdf->useTemplate($tplId);

        $pdfString = $pdf->Output('S');

//        file_put_contents('out.pdf', $pdfString);

        $parser = new PdfParser(StreamReader::createByString($pdfString));
        $object = $parser->getIndirectObject(2);

        $objectId = $object->value->value['XObject']->value['TPL0']->value;
        $this->assertEquals(7, $objectId);

        $object = $parser->getIndirectObject($objectId);
        $expectedStream = "2 J\n"
            . "0.57 w\n"
            . "q 283.46 0 0 566.93 0.00 0.00 cm /I1 Do Q\n";
        $this->assertEquals($expectedStream, $object->value->getUnfilteredStream());

        $reader = new PdfReader($parser);
        $expectedStream = "2 J\n"
            . "0.57 w\n"
            . "q 0 J 1 w 0 j 0 G 0 g 1.0000 0 0 1.0000 0.0000 274.9609 cm /TPL0 Do Q\n";

        $this->assertEquals($expectedStream, $reader->getPage(1)->getContentStream());

        $xObjects = PdfType::resolve($object->value->value->value['Resources'], $parser)->value['XObject'];
        $this->assertTrue(isset($xObjects->value['I1']));

        $imageObject = PdfType::resolve($xObjects->value['I1'], $parser);
        $this->assertInstanceOf(PdfStream::class, $imageObject);
        $this->assertEquals(256, $imageObject->value->value['Width']->value);
        $this->assertEquals(256, $imageObject->value->value['Height']->value);
    }

    public function testTemplateInTempalte()
    {
        $pdf = new FpdfTpl('P', 'pt');
        $pdf->SetCompression(false);
        $tplA = $pdf->beginTemplate(100, 100);
        $pdf->Image(__DIR__ .'/../_files/images/png-8.png', 0, 0, 100, 100);
        $pdf->endTemplate();

        $tplB = $pdf->beginTemplate(200, 200);
        $pdf->useTemplate($tplA, 0, 0, 150, 150);
        $pdf->Image(__DIR__ . '/../_files/images/jpeg.jpg', 75, 75, 125);
        $pdf->endTemplate();

        $pdf->AddPage();
        $pdf->useTemplate($tplB, 0, 0, 100);
        $pdf->SetDrawColor(255, 100, 100);
        $pdf->Rect(0, 0, 100, 100);

        $pdfString = $pdf->Output('S');
//        file_put_contents('out.pdf', $pdfString);

        $parser = new PdfParser(StreamReader::createByString($pdfString));
        $pageObject = $parser->getIndirectObject(3);
        $pageContentObject = PdfType::resolve($pageObject->value->value['Contents'], $parser);
        $expectedStream = "2 J\n"
            . "0.57 w\n"
            . "q 0 J 1 w 0 j 0 G 0 g 0.5000 0 0 0.5000 0.0000 741.8900 cm /TPL1 Do Q\n"
            . "1.000 0.392 0.392 RG\n"
            . "0.00 841.89 100.00 -100.00 re S\n";
        $this->assertEquals($expectedStream, $pageContentObject->getUnfilteredStream());

        $pageResources = PdfType::resolve($pageObject->value->value['Resources'], $parser);
        $tplBObject = PdfType::resolve($pageResources->value['XObject']->value['TPL1'], $parser);

        $expectedStream = "2 J\n"
            . "0.57 w\n"
            . "q 0 J 1 w 0 j 0 G 0 g 1.5000 0 0 1.5000 0.0000 50.0000 cm /TPL0 Do Q\n"
            . "q 125.00 0 0 125.00 75.00 0.00 cm /I2 Do Q\n";
        $this->assertEquals($expectedStream, $tplBObject->getUnfilteredStream());

        $tplAObject = PdfType::resolve(
            PdfType::resolve($tplBObject->value->value['Resources'], $parser)->value['XObject']->value['TPL0'],
            $parser
        );

        $expectedStream = "2 J\n"
            . "0.57 w\n"
            . "q 100.00 0 0 100.00 0.00 0.00 cm /I1 Do Q\n";
        $this->assertEquals($expectedStream, $tplAObject->getUnfilteredStream());
    }

    public function testFontInTemplate()
    {
        $pdf = new FpdfTpl('P', 'pt');
        $tplId = $pdf->beginTemplate(100, 100);
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetXY(0, 0);
        $pdf->SetFont('arial', '', 20);
        $pdf->Write(20 * 1.2, 'A simple text in a template');
        $pdf->endTemplate();

        $pdf->AddPage();
        $size = $pdf->useTemplate($tplId);
        $pdf->Rect(0, 0, $size['width'], $size['height']);

        $pdfString = $pdf->Output('S');
//        file_put_contents('out.pdf', $pdfString);

        $parser = new PdfParser(StreamReader::createByString($pdfString));
        $pageObject = $parser->getIndirectObject(3);
        $pageContentObject = PdfType::resolve($pageObject->value->value['Contents'], $parser);
        $expectedStream = "2 J\n"
            . "0.57 w\n"
            . "q 0 J 1 w 0 j 0 G 0 g 1.0000 0 0 1.0000 0.0000 741.8900 cm /TPL0 Do Q\n"
            . "0.00 841.89 100.00 -100.00 re S\n";
        $this->assertEquals($expectedStream, $pageContentObject->getUnfilteredStream());

        $pageResources = PdfType::resolve($pageObject->value->value['Resources'], $parser);

        $tplObject = PdfType::resolve($pageResources->value['XObject']->value['TPL0'], $parser);

        $expectedStream = "2 J\n"
            . "0.57 w\n"
            . "BT /F1 20.00 Tf ET\n"
            . "BT 2.83 82.00 Td (A simple) Tj ET\n"
            . "BT 2.83 58.00 Td (text in a) Tj ET\n"
            . "BT 2.83 34.00 Td (template) Tj ET\n";
        $this->assertEquals($expectedStream, $tplObject->getUnfilteredStream());

        $fonts = PdfType::resolve($tplObject->value->value['Resources'], $parser)->value['Font'];
        $this->assertTrue(isset($fonts->value['F1']));
    }

    public function testCopyOfFpdfToTemplate()
    {
        $pdf = new FpdfTpl('P', 'pt');
        $pdf->AddPage();
        $pdf->SetDrawColor(255, 0, 0);
        $pdf->SetFillColor(0, 255, 0);
        $pdf->SetTextColor(0, 0, 255);
        $pdf->SetFont('Helvetica', '', 12);

        $tplIdx = $pdf->beginTemplate(100, 12);
        $pdf->SetXY(0, 0);
        $pdf->Cell(100, 12, 'My Test Template', 1, 0, '', 'FD');
        $pdf->endTemplate();

        $pdf->useTemplate($tplIdx, 10, 10, 400);

        $pdfString = $pdf->Output('S');
//        file_put_contents('out.pdf', $pdfString);

        $parser = new PdfParser(StreamReader::createByString($pdfString));
        $pageObject = $parser->getIndirectObject(3);
        $pageContentObject = PdfType::resolve($pageObject->value->value['Contents'], $parser);
        $expectedStream = "2 J\n"
            . "0.57 w\n"
            . "1.000 0.000 0.000 RG\n"
            . "0.000 1.000 0.000 rg\n"
            . "BT /F1 12.00 Tf ET\n"
            . "q 0 J 1 w 0 j 0 G 0 g 4.0000 0 0 4.0000 10.0000 783.8900 cm /TPL0 Do Q\n";
        $this->assertEquals($expectedStream, $pageContentObject->getUnfilteredStream());

        $pageResources = PdfType::resolve($pageObject->value->value['Resources'], $parser);

        $tplObject = PdfType::resolve($pageResources->value['XObject']->value['TPL0'], $parser);

        $expectedStream = "2 J\n"
            . "0.57 w\n"
            . "BT /F1 12.00 Tf ET\n"
            . "1.000 0.000 0.000 RG\n"
            . "0.000 1.000 0.000 rg\n"
            . "0.00 12.00 100.00 -12.00 re B q 0.000 0.000 1.000 rg BT 2.83 2.40 Td (My Test Template) Tj ET Q\n";
        $this->assertEquals($expectedStream, $tplObject->getUnfilteredStream());
    }
}