<?php

namespace setasign\Fpdi\unit;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\FpdfTpl;

require_once __DIR__ . '/FpdiTraitTest.php';

class FpdfTplTest extends TestCase
{
    /**
     * @expectedException \BadMethodCallException
     */
    public function testAddPageInTemplate()
    {
        $pdf = new FpdfTpl();
        $pdf->beginTemplate();
        $pdf->AddPage();
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testSetPageFormatInTemplate()
    {
        $pdf = new FpdfTpl();
        $pdf->beginTemplate();
        $pdf->setPageFormat([10, 10], 'L');
    }
}
