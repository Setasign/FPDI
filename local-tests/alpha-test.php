<?php
/**
 * Simply test the behaviour when importing pages which uses a graphic state with opacity values
 * into a page, that also uses such graphic state (handling of $groupXObject parameter of the importPage()).
 */

use setasign\Fpdi;

require_once '../vendor/autoload.php';

$start = microtime(true);

//class AlphaPDF extends Fpdi\Tfpdf\Fpdi
class AlphaPDF extends Fpdi\Fpdi
{
    var $extgstates = array();

    // alpha: real value from 0 (transparent) to 1 (opaque)
    // bm:    blend mode, one of the following:
    //          Normal, Multiply, Screen, Overlay, Darken, Lighten, ColorDodge, ColorBurn,
    //          HardLight, SoftLight, Difference, Exclusion, Hue, Saturation, Color, Luminosity
    function SetAlpha($alpha, $bm='Normal')
    {
        // set alpha for stroking (CA) and non-stroking (ca) operations
        $gs = $this->AddExtGState(array('ca'=>$alpha, 'CA'=>$alpha, 'BM'=>'/'.$bm));
        $this->SetExtGState($gs);
    }

    function AddExtGState($parms)
    {
        $n = count($this->extgstates)+1;
        $this->extgstates[$n]['parms'] = $parms;
        return $n;
    }

    function SetExtGState($gs)
    {
        $this->_out(sprintf('/GS%d gs', $gs));
    }

    function _enddoc()
    {
        if(!empty($this->extgstates) && $this->PDFVersion<'1.4')
            $this->PDFVersion='1.4';
        parent::_enddoc();
    }

    function _putextgstates()
    {
        for ($i = 1; $i <= count($this->extgstates); $i++)
        {
            $this->_newobj();
            $this->extgstates[$i]['n'] = $this->n;
            $this->_out('<</Type /ExtGState');
            $parms = $this->extgstates[$i]['parms'];
            $this->_out(sprintf('/ca %.3F', $parms['ca']));
            $this->_out(sprintf('/CA %.3F', $parms['CA']));
            $this->_out('/BM '.$parms['BM']);
            $this->_out('>>');
            $this->_out('endobj');
        }
    }

    function _putresourcedict()
    {
        parent::_putresourcedict();
        $this->_out('/ExtGState <<');
        foreach($this->extgstates as $k=>$extgstate)
            $this->_out('/GS'.$k.' '.$extgstate['n'].' 0 R');
        $this->_out('>>');
    }

    function _putresources()
    {
        $this->_putextgstates();
        parent::_putresources();
    }
}


$pdf = new AlphaPDF();

$pdf->AddPage();

$pageCount = $pdf->setSourceFile(__DIR__ . '/../tests/_files/pdfs/transparency/ex74.pdf');
$tplIdA = $pdf->importPage(1, 'CropBox', true);
$tplIdB = $pdf->importPage(1, 'CropBox', false);

$pdf->SetAlpha(.1);

$pdf->useTemplate($tplIdA, 40, 50, 100);
$pdf->useTemplate($tplIdB, 160, 50, 100);

$pdf->Output('alpha-test.pdf', 'F');

echo microtime(true) - $start;
echo "<br>";
var_dump(memory_get_peak_usage());
echo "<br>";
echo filesize('alpha-test.pdf');

?>

<iframe src="http://pdfanalyzer2.dev1.setasign.local/plugin?file=<?php echo urlencode(realpath('alpha-test.pdf')); ?>" width="100%" height="96%"></iframe>
