<?php

namespace setasign\Fpdi\unit\Tcpdf;

use setasign\Fpdi\Tcpdf\Fpdi;
use setasign\Fpdi\unit\FpdiTraitTest;

class FpdiTest extends FpdiTraitTest
{
    public function getInstance()
    {
        return new Fpdi();
    }
}
