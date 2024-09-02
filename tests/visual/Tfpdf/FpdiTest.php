<?php

namespace Tfpdf;

use setasign\Fpdi\Tfpdf\Fpdi;

class FpdiTest extends \setasign\Fpdi\visual\FpdiTest
{
    /**
     * Should return __FILE__
     *
     * @return string
     */
    public function getClassFile()
    {
        return __FILE__;
    }

    public function getInstance($unit = 'pt')
    {
        return new Fpdi('P', $unit);
    }
}
