<?php

namespace setasign\Fpdi\functional;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\Fpdi;

class ReleaseCycledReferencesTest extends TestCase
{
    public function testRenameFileAfterSetSourceFile()
    {
        $pdf = new Fpdi();

        $originalFile = __DIR__ . '/../_files/pdfs/Boombastic-Box.pdf';
        $file = __DIR__ . '/ResolveCycledReferencesTest_testRenameFileAfterSetSourceFile.pdf';
        $newFile = __DIR__ . '/ResolveCycledReferencesTest_testRenameFileAfterSetSourceFile2.pdf';

        try {
            copy($originalFile, $file);

            $pdf->setSourceFile($file);
            unset($pdf);

            rename($file, $newFile);
        } finally {
            @unlink($file);
            @unlink($newFile);
        }
    }
}
