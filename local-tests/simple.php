<?php
/**
 * Simply import all pages and different bounding boxes from different PDF documents.
 */
use setasign\Fpdi;

require_once '../vendor/autoload.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(20);
date_default_timezone_set('UTC');
$start = microtime(true);

//$pdf = new Fpdi\Fpdi('L', 'mm', 'A3');
//$pdf = new Fpdi\Tcpdf\Fpdi('L', 'mm', 'A3');
$pdf = new Fpdi\Tfpdf\Fpdi('L', 'mm', 'A3');

if ($pdf instanceof \TCPDF) {
    $pdf->SetProtection(['print'], '', 'owner');
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
}

$files = [
//    __DIR__ . '/../tests/_files/pdfs/Fantastic-Speaker.pdf',
//    __DIR__ . '/../tests/_files/pdfs/links/links.pdf',
    __DIR__ . '/../tests/_files/pdfs/links/boxes.pdf',
//    __DIR__ . '/../tests/_files/pdfs/stamps/ENU/StandardBusiness.pdf',
//    __DIR__ . '/../tests/_files/pdfs/tektown/Logo.pdf',
//    __DIR__ . '/../tests/_files/pdfs/1000.pdf',
//    __DIR__ . '/../tests/_files/pdfs/boxes/All.pdf',
//    __DIR__ . '/../tests/_files/pdfs/boxes/All2.pdf',
//    __DIR__ . '/../tests/_files/pdfs/boxes/[1000 500 -1000 -500].pdf',
//    __DIR__ . '/../tests/_files/pdfs/boxes/[1000 500 -1000 -500]-R90.pdf',
];

foreach ($files as $file) {
    $pageCount = $pdf->setSourceFile($file);

    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
        $pdf->AddPage('L', 'A3');
        $pageId = $pdf->importPage($pageNo, Fpdi\PdfReader\PageBoundaries::MEDIA_BOX, true, true);
        $pdf->useTemplate($pageId, 20, 20, 100, 100);
        $pdf->Rect(20, 20, 100, 100);
        #var_dump($pageId);

        $pageId = $pdf->importPage($pageNo, Fpdi\PdfReader\PageBoundaries::ART_BOX, true, true);
        $s = $pdf->useTemplate($pageId, 120, 120, 100);
        #$s = $pdf->useTemplate($pageId, null, null, 100, null, true);
        $pdf->Rect(120, 120, $s['width'], $s['height']);

        $s = $pdf->useTemplate($pageId, 220, 120, 100, 100);
        $pdf->Rect(220, 120, $s['width'], $s['height']);
        #var_dump($pageId);

        #break;
    }
}

//$pdf->Output('F', 'simple.pdf');
$pdf->Output(realpath('simple.pdf'), 'F');

echo microtime(true) - $start;
echo "<br>";
var_dump(memory_get_usage());
unset($pdf);
var_dump(gc_collect_cycles());
echo "<br>";
var_dump(memory_get_usage());
echo "<br>";
echo filesize('simple.pdf');
?>

<iframe src="http://pdfanalyzer2.dev1.setasign.local/plugin?file=<?php echo urlencode(realpath('simple.pdf')); ?>" width="100%" height="98%"></iframe>
