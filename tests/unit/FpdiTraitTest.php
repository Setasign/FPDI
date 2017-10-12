<?php

namespace setasign\Fpdi\unit;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\FpdiTrait;

abstract class FpdiTraitTest extends TestCase
{
    /**
     * @return FpdiTrait
     */
    abstract public function getInstance();

    /**
     * @expectedException \BadMethodCallException
     */
    public function testImportedPageWithoutSettingSourceFile()
    {
        $fpdi = $this->getInstance();
        $fpdi->importPage(1);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testUseImportedPageWithoutSettingSourceFile()
    {
        $fpdi = $this->getInstance();
        $fpdi->AddPage();
        $fpdi->useImportedPage(1);
    }
}
