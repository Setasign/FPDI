<?php

set_time_limit(120);
ini_set('memory_limit', '512M');
error_reporting(E_ALL);
ini_set('display_errors', 1);


$fpdiLegacy = __DIR__ . '/../../FPDI/classes/fpdi.php';

$GLOBALS['paths'] = [
    __DIR__ . '/../tests/_files/',
    '/var/www/privatewebs/jan.slabon/default/html/pdfs/'
];

require 'filelist.php';

$files = $_GET['f'];
if (!is_array($files)) {
    $files = [$files];
}

if (isset($_GET['useLegacy']) && $_GET['useLegacy'] == '1') {
    $useLegacy = true;
} else {
    $useLegacy = false;
}

echo '<a href="' . parse_url($_SERVER['REQUEST_URI'])['path'] . '?' . http_build_query(['f' => $files, 'useLegacy' => !$useLegacy]) . '">';
if ($useLegacy) {
    echo '<b>NORMAL MODE</b>';
} else {
    echo '<b>LEGACY MODE</b>';
}
echo '</a><br/>';

if ($useLegacy && !file_exists($fpdiLegacy)) {
    echo 'FPDI is not installed in ' . $fpdiLegacy . '<br/>';
    die();
}

if ($useLegacy) {
    require_once $fpdiLegacy;
}

$start = microtime(true);

if ($useLegacy) {
    $pdf = new \FPDI('P', 'pt', 'A3');
} else {
    $pdf = new setasign\Fpdi\Fpdi('P', 'pt', 'A3');
}

foreach ($files as $file) {
    $pageCount = $pdf->setSourceFile($file);

    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
        $pdf->AddPage();

        $pageId = $pdf->importPage($pageNo, 'TrimBox', true, true);
        if ($useLegacy) {
            $pdf->useTemplate($pageId, null, null, 0, 0, true);
        } else {
            $pdf->useTemplate($pageId, 0, 0, null, null, true);
        }
    }
}

$pdf->Output('F', 'concatenate.pdf');

echo microtime(true) - $start;
echo "<br>";
var_dump(memory_get_peak_usage());
echo "<br>";
unset($pdf);
gc_collect_cycles();
var_dump(memory_get_usage());
echo "<br>";
echo filesize('concatenate.pdf');
echo "<br>";

?>
<iframe src="http://pdfanalyzer2.dev1.setasign.local/plugin?file=<?php echo urlencode(realpath('concatenate.pdf')); ?>"
        width="100%" height="94%"></iframe>