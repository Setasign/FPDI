<?php
/**
 * Let's simply try tFPDF with some unicode text and an imported page via FPDI.
 */

use setasign\Fpdi\Tfpdf\Fpdi;

require_once '../vendor/autoload.php';

$pdf = new Fpdi();

$pdf->AddPage();

$pdf->setSourceFile(__DIR__ . '/../tests/_files/pdfs/tektown/Letterhead.pdf');
$tplId = $pdf->importPage(1);
$pdf->useTemplate($tplId);


// Add a Unicode font (uses UTF-8)
$pdf->AddFont('DejaVu', '', 'DejaVuSansCondensed.ttf',true);
$pdf->SetFont('DejaVu', '', 14);
$txt = file_get_contents(__DIR__ . '/../vendor/setasign/tfpdf/HelloWorld.txt');

$tplId2 = $pdf->beginTemplate();
$pdf->Write(8, $txt);
$pdf->endTemplate();

$pdf->useTemplate($tplId2, 0, 30);
$pdf->useTemplate($tplId2, $pdf->GetPageWidth() / 2, 30);

// Select a standard font (uses windows-1252)
$pdf->SetFont('Arial', '', 14);
$pdf->Ln(100);
$pdf->Write(5, 'Some example text in Helvetica.');
$pdf->Output();