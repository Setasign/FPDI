<?php

namespace setasign\Fpdi\unit;

use setasign\Fpdi\TcpdfFpdi;

require_once __DIR__ . '/FpdiTraitTest.php';

class TcpdfFpdiTest extends FpdiTraitTest
{
    public function getInstance()
    {
        return new TcpdfFpdi();
    }
}
