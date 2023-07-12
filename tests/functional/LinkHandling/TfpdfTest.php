<?php

namespace setasign\Fpdi\functional\LinkHandling;

use setasign\Fpdi\Tfpdf\Fpdi;

class TfpdfTest extends \setasign\Fpdi\functional\LinkHandling\AbstractTest
{
    protected function getInstance($orientation = 'P', $unit = 'mm', $size = 'A4')
    {
        return new Fpdi($orientation, $unit, $size);
    }
}
