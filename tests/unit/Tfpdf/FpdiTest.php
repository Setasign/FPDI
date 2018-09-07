<?php

namespace setasign\Fpdi\unit\Tfpdf;

use setasign\Fpdi\Tfpdf\Fpdi;
use setasign\Fpdi\unit\FpdiTraitTest;

class FpdiTest extends FpdiTraitTest
{
    public function getInstance()
    {
        return new Fpdi();
    }
}
