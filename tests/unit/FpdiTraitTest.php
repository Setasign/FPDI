<?php

namespace setasign\Fpdi\unit;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\FpdiTrait;

abstract class FpdiTraitTest extends TestCase
{
    /**
     * @return FpdiTrait
     */
    abstract public function getInstance();

    public function testImportedPageWithoutSettingSourceFile()
    {
        $fpdi = $this->getInstance();
        $this->expectException(\BadMethodCallException::class);
        $fpdi->importPage(1);
    }

    public function testUseImportedPageWithoutSettingSourceFile()
    {
        $fpdi = $this->getInstance();
        $fpdi->AddPage();
        $this->expectException(\InvalidArgumentException::class);
        $fpdi->useImportedPage(1);
    }
}
