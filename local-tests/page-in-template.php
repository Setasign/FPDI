<?php

use setasign\Fpdi\Fpdi;

require_once '../vendor/autoload.php';

$pdf = new Fpdi('P', 'mm');
$pdf->setSourceFile(__DIR__ . '/../tests/_files/pdfs/Boombastic-Box.pdf');
$pageId = $pdf->importPage(1);

$pdf->beginTemplate();
$size = $pdf->useImportedPage($pageId, 10, 10, 100);
$pdf->SetDrawColor(0, 255, 0);
$pdf->Rect(10, 10, $size['width'], $size['height']);
$tplId = $pdf->endTemplate();

$pdf->AddPage();
$x = 10;
$y = 20;
$width = 190;

$size = $pdf->useTemplate($tplId, $x, $y, $width);

$pdf->SetDrawColor(255, 0, 0);
$pdf->Rect($x, $y, $size['width'], $size['height']);

$pdf->Output('F', 'page-in-template.pdf');

?>
<iframe src="http://pdfanalyzer2.dev1.setasign.local/plugin?file=<?php echo urlencode(realpath('page-in-template.pdf')); ?>" width="100%" height="98%"></iframe>
