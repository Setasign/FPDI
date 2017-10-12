<?php

namespace setasign\Fpdi\unit;

use setasign\Fpdi\TcpdfFpdi;

class TcpdfFpdiTest extends FpdiTraitTest
{
    public function getInstance()
    {
        return new TcpdfFpdi();
    }
}
