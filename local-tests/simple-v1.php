<?php
/**
 * Simply import all pages and different bounding boxes from different PDF documents with version 1.
 */
set_time_limit(120);
require_once '../vendor/autoload.php';
require_once '../../FPDI/classes/fpdi.php';

$start = microtime(true);

$pdf = new FPDI('L', 'mm', 'A3');

$files = [
//    __DIR__ . '/../tests/_files/pdfs/stamps/ENU/StandardBusiness.pdf',
//    __DIR__ . '/../tests/_files/pdfs/tektown/Logo.pdf',
//    __DIR__ . '/../tests/_files/pdfs/1000.pdf',
    __DIR__ . '/../tests/_files/pdfs/boxes/All.pdf',
    __DIR__ . '/../tests/_files/pdfs/boxes/All2.pdf',
    __DIR__ . '/../tests/_files/pdfs/boxes/[1000 500 -1000 -500].pdf',
    __DIR__ . '/../tests/_files/pdfs/boxes/[1000 500 -1000 -500]-R90.pdf',
];

foreach ($files as $file) {
    $pageCount = $pdf->setSourceFile($file);

    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
        $pdf->AddPage();
        $pageId = $pdf->importPage($pageNo, 'MediaBox');
        $pdf->useTemplate($pageId, 20, 20, 100, 100);
        $pdf->Rect(20, 20, 100, 100);
        #var_dump($pageId);

        $pageId = $pdf->importPage($pageNo, 'ArtBox');
        $s = $pdf->useTemplate($pageId, 120, 120, 100);
        $pdf->Rect(120, 120, $s['w'], $s['h']);

        $s = $pdf->useTemplate($pageId, 220, 120, 100, 100);
        $pdf->Rect(220, 120, $s['w'], $s['h']);
        #var_dump($pageId);

        #break;
    }
}

$pdf->Output('F', 'simple-v1.pdf');

echo microtime(true) - $start;
echo "<br>";
var_dump(memory_get_usage());
unset($pdf);
var_dump(gc_collect_cycles());
echo "<br>";
var_dump(memory_get_usage());
echo "<br>";
echo filesize('simple-v1.pdf');
?>

<iframe src="http://pdfanalyzer2.dev1.setasign.local/plugin?file=<?php echo urlencode(realpath('simple-v1.pdf')); ?>" width="100%" height="98%"></iframe>
