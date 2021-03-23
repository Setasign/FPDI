<?php

use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\Type\PdfDictionary;
use setasign\Fpdi\PdfParser\Type\PdfHexString;
use setasign\Fpdi\PdfParser\Type\PdfName;
use setasign\Fpdi\PdfParser\Type\PdfString;
use setasign\Fpdi\PdfParser\Type\PdfNumeric;
use setasign\Fpdi\PdfParser\Type\PdfBoolean;
use setasign\Fpdi\PdfParser\Type\PdfType;

require_once '../vendor/autoload.php';

$GLOBALS['paths'] = [
    __DIR__ . '/../tests/_files/',
    '/var/www/privatewebs/jan.slabon/default/html/pdfs/'
];

require 'filelist.php';

class FpdiWithInfo extends Fpdi
{
    /**
     * Returns the data of the Info dictionary (only scalar values are returned).
     *
     * @return array
     * @throws \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException
     * @throws \setasign\Fpdi\PdfParser\PdfParserException
     * @throws \setasign\Fpdi\PdfParser\Type\PdfTypeException
     */
    public function getInfoFromSourceFile()
    {
        if ($this->currentReaderId === null) {
            throw new \BadMethodCallException('No reader initiated. Call setSourceFile() first.');
        }

        $reader = $this->getPdfReader($this->currentReaderId);
        $parser = $reader->getParser();
        $trailer = $reader->getParser()->getCrossReference()->getTrailer();

        $info = PdfType::resolve(PdfDictionary::get($trailer, 'Info'), $parser);

        $result = [];
        if ($info instanceof PdfDictionary) {
            foreach ($info->value as $key => $value) {
                $value = PdfType::resolve($value, $parser);
                switch (get_class($value)) {
                    case PdfString::class:
                        $result[$key] = self::pdfStringToUtf8(PdfString::unescape($value->value));
                        break;
                    case PdfHexString::class:
                        $result[$key] = self::pdfStringToUtf8(\hex2bin($value->value));
                        break;
                    case PdfName::class:
                        $result[$key] = PdfName::unescape($value->value);
                        break;
                    case PdfNumeric::class:
                    case PdfBoolean::class:
                        $result[$key] = $value->value;
                        break;
                }
            }
        }

        return $result;
    }

    /**
     * Converts a PDF string to UTF-8 encoding.
     *
     * @param string $string
     * @return false|string
     */
    protected static function pdfStringToUtf8($string)
    {
        static $table;

        if (strpos($string, "\xFE\xFF") === 0 || strpos($string, "\xFF\xFE") === 0) {
            return \iconv('UTF-16', 'UTF-8', $string);
        }

        if ($table === null) {
            $table = \json_decode(file_get_contents('assets/pdfdoc-encoding.json'), true);
        }

        $result = '';
        for ($pos = 0, $len = strlen($string); $pos < $len; $pos++) {
            $result .= $table[ord($string[$pos])];
        }

        return $result;
    }
}


$files = $_GET['f'];
if (!is_array($files)) {
    $files = [$files];
}

foreach ($files as $file) {
    $pdf = new FpdiWithInfo();
    $pdf->setSourceFile($file);

    echo "<pre>";
    var_dump($pdf->getInfoFromSourceFile());
    echo "</pre>";
}
