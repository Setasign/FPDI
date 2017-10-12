<?php
/**
 * Test script to evaluate memory usage and possible memory leaks (as with TCPDF).
 */
set_time_limit(90);

use setasign\Fpdi;

require_once '../vendor/autoload.php';

$start = microtime(true);

$files = [
    __DIR__ . '/../tests/_files/pdfs/stamps/ENU/StandardBusiness.pdf',
    __DIR__ . '/../tests/_files/pdfs/tektown/Logo.pdf',
//    __DIR__ . '/../tests/_files/pdfs/1000.pdf',
//    __DIR__ . '/../tests/_files/pdfs/10000.pdf',
    __DIR__ . '/../tests/_files/pdfs/boxes/All2.pdf',
    __DIR__ . '/../tests/_files/pdfs/Boombastic-Box.pdf',
    __DIR__ . '/../tests/_files/pdfs/Fantastic-Speaker.pdf',
    __DIR__ . '/../tests/_files/pdfs/Word2010.pdf',
];

$files = array_merge($files, $files, $files, $files, $files, $files, $files, $files, $files, $files, $files, $files);
$files = array_merge($files, $files);

foreach ($files as $file) {
    $pdf = new Fpdi\Fpdi('L', 'mm', 'A3');
    #$pdf = new Fpdi\TcpdfFpdi('L', 'mm', 'A3'); // has memory leaks

    if ($pdf instanceof \TCPDF) {
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
    }

    $pageCount = $pdf->setSourceFile($file);

    for ($pageNo = 1; $pageNo <= min($pageCount, 1000); $pageNo++) {
        $pdf->AddPage();
        $pageId = $pdf->importPage($pageNo, Fpdi\PdfReader\PageBoundaries::MEDIA_BOX);
        $pdf->useTemplate($pageId, 20, 20, 100, 100);
        $pdf->Rect(20, 20, 100, 100);
        #var_dump($pageId);

        $pageId = $pdf->importPage($pageNo, Fpdi\PdfReader\PageBoundaries::ART_BOX);
        $s = $pdf->useTemplate($pageId, 120, 120, 100);
        $pdf->Rect(120, 120, $s['width'], $s['height']);

        //    $s = $pdf->useTemplate($pageId, 220, 120, 100, 100);
        //    $pdf->Rect(220, 120, $s['width'], $s['height']);
        #var_dump($pageId);

        #break;
    }

    $pdf->Output(realpath('memory.pdf'), 'F');

    echo microtime(true) - $start;
    echo "<br>";
    var_dump(memory_get_usage());
    unset($pdf, $files, $pageNo, $file, $pageCount, $pageId, $s);
    var_dump(gc_collect_cycles());
    echo "<br>";
    var_dump(memory_get_usage());
    echo "<br>";
    echo filesize('memory.pdf');
    echo "<br>";
    echo "<br>";

}



?>

<iframe src="http://pdfanalyzer2.dev1.setasign.local/plugin?file=<?php echo urlencode(realpath('memory.pdf')); ?>" width="100%" height="96%"></iframe>
