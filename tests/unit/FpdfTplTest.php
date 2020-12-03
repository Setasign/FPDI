<?php

namespace setasign\Fpdi\unit;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\FpdfTpl;

require_once __DIR__ . '/FpdiTraitTest.php';

class FpdfTplTest extends TestCase
{
    public function testAddPageInTemplate()
    {
        $pdf = new FpdfTpl();
        $pdf->beginTemplate();
        $this->expectException(\BadMethodCallException::class);
        $pdf->AddPage();
    }

    public function testSetPageFormatInTemplate()
    {
        $pdf = new FpdfTpl();
        $pdf->beginTemplate();
        $this->expectException(\BadMethodCallException::class);
        $pdf->setPageFormat([10, 10], 'L');
    }
}
