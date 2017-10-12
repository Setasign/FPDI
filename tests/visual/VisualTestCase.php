<?php

namespace setasign\Fpdi\visual;

use PHPUnit\Framework\TestCase;

abstract class VisualTestCase extends TestCase
{
    abstract public function createProvider();

    /**
     * Should return __FILE__
     *
     * @return string
     */
    abstract public function getClassFile();

    public function createImage($inputFile, $tmpDir, $dpi = 150)
    {
        if (!file_exists($tmpDir)) {
            $old = umask(0);
            if (!@mkdir($tmpDir, 0775, true) && !is_dir($tmpDir)) {
                throw new \Exception(sprintf(
                    'Couldn\'t create tmpDir "%s"',
                    $tmpDir
                ));
            }
            umask($old);
        }

        exec(
            'mutool draw -o "' . $tmpDir . '/%d.png" -r ' . $dpi . ' -A 8 "' . $inputFile . '" 2>&1',
            $output,
            $status
        );

        $old = umask(0);
        foreach (glob($tmpDir . '/*.png') as $filename) {
            chmod($filename, 0775);
        }
        umask($old);

        if ($status != 0) {
            $this->fail(implode("\n", $output));
        }
    }

    /**
     * @dataProvider createProvider
     */
    public function testCreate($inputData, $tolerance, $dpi = 150)
    {
        $classFile = $this->getClassFile();

        $tmpPath = is_array($inputData)
            ? $inputData['tmpPath']
            : basename($inputData);

        $tmpDir = dirname($classFile) . DIRECTORY_SEPARATOR
            . pathinfo($classFile, PATHINFO_FILENAME). DIRECTORY_SEPARATOR
            . $tmpPath . DIRECTORY_SEPARATOR
            . 'compare';

        if (!file_exists($tmpDir)) {
            $old = umask(0);
            if (!@mkdir($tmpDir, 0775, true) && !is_dir($tmpDir)) {
                throw new \Exception(sprintf(
                    'Couldn\'t create tmpDir "%s"',
                    $tmpDir
                ));
            }
            umask($old);
        }

        $outputFile = realpath($tmpDir) . '/result.pdf';

        if (isset($inputData['_method'])) {
            $method = $inputData['_method'];
            $this->$method($inputData, $outputFile);
        } elseif (method_exists($this, 'createPDF')) {
            $this->createPDF($inputData, $outputFile);
        }

        $old = umask(0);
        chmod($outputFile, 0775);
        umask($old);

        $this->createImage($outputFile, $tmpDir, $dpi);

        $esc = function ($path) {
            return preg_replace('/(\*|\?|\[)/', '[$1]', $path);
        };

        $originalImages = array();
        $testImages = array();
        foreach (glob($esc($tmpDir) . '/../original/*.png') as $filename) {
            $originalImages[] = $filename;
        }

        $diff = $tmpDir . '/diff.png';
        if (file_exists($diff)) {
            unlink($diff);
        }

        foreach (glob($esc($tmpDir) . '/*.png') as $filename) {
            $testImages[] = $filename;
        }

        $this->assertEquals(count($originalImages), count($testImages), 'Count of pages for file '.$tmpPath);

        foreach ($originalImages as $k => $filename) {
            $out = exec(sprintf(
                'compare -alpha on -metric mae "%s" "%s" "%s" 2>&1',
                $originalImages[$k],
                $testImages[$k],
                $diff
            ));

            if (file_exists($diff)) {
                $old = umask(0);
                chmod($diff, 0775);
                umask($old);
            }

            $this->assertNotEquals(
                0,
                preg_match('~^[0-9.]*(\s\([0-9e.\-]*\))?$~', $out),
                $out . ' for file ' . $tmpPath
            );
            //var_dump($out);
            $this->assertLessThan($tolerance, $out, 'Page '. $filename.' for file '.$tmpPath);

            unlink($diff);
        }

        //clean up
        foreach ($testImages as $filename) {
            unlink($filename);
        }
        unlink($tmpDir . DIRECTORY_SEPARATOR . 'result.pdf');
        rmdir($tmpDir);
    }
}
