<?php

use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\Type\PdfDictionary;
use setasign\Fpdi\PdfParser\Type\PdfNull;
use setasign\Fpdi\PdfParser\Type\PdfType;

require_once '../vendor/autoload.php';

$GLOBALS['paths'] = [
    __DIR__ . '/../tests/_files/',
    '/var/www/privatewebs/jan.slabon/default/html/pdfs/'
];

require 'filelist.php';

/**
 * Class FpdiWithLayers
 *
 * This class imports the layer definition of the first file with layer/optional content properties information.
 */
class FpdiWithLayers extends Fpdi
{
    protected $ocPropertiesN;

    protected function _putresources()
    {
        $this->currentReaderId = null;
        foreach (\array_keys($this->readers) as $readerId) {
            $reader = $this->getPdfReader($readerId);
            $parser = $reader->getParser();
            $this->currentReaderId = $readerId;

            $ocProperties = PdfType::resolve(PdfDictionary::get($parser->getCatalog(), 'OCProperties'), $parser);
            if (!($ocProperties instanceof PdfNull)) {
                $ocProperties = PdfDictionary::ensure($ocProperties);
                $this->_newobj();
                $this->ocPropertiesN = $this->n;
                $this->writePdfType($ocProperties);
                $this->_out('endobj');
                break;
            }
        }

        parent::_putresources();
    }

    protected function _putcatalog()
    {
        $this->_out('/OCProperties ' . $this->ocPropertiesN . ' 0 R');
        parent::_putcatalog();
    }
}

$files = $_GET['f'];
if (!is_array($files)) {
    $files = [$files];
}

$pdf = new FpdiWithLayers();

foreach ($files as $file) {
    $pageCount = $pdf->setSourceFile($file);

    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
        $pdf->AddPage();
        $pdf->useTemplate($pdf->importPage($pageNo), ['adjustPageSize' => true]);
    }
}

$pdf->Output('F', 'import-layers.pdf');

?>
<iframe src="http://pdfanalyzer2.dev1.setasign.local/plugin?file=<?php echo urlencode(realpath('import-layers.pdf')); ?>"
        width="100%" height="94%"></iframe>
