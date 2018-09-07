<?php

namespace setasign\Fpdi\visual\Tfpdf;

use setasign\Fpdi\Tfpdf\FpdfTpl;

class FpdfTplTest extends \setasign\Fpdi\visual\FpdfTplTest
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

    public function getInstance()
    {
        return new FpdfTpl('P', 'pt');
    }
}
