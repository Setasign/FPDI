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

    /**
     * @param string $inputFile
     * @param string $tmpDir
     * @param int $dpi
     */
    public function createImage($inputFile, $tmpDir, $dpi = 150)
    {
        if (!is_dir($tmpDir)) {
            $old = umask(0);
            if (!@mkdir($tmpDir, 0775, true) && !is_dir($tmpDir)) {
                throw new \RuntimeException(sprintf(
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
        foreach (glob($tmpDir . '/*.png', GLOB_NOSORT) as $filename) {
            chmod($filename, 0775);
        }
        umask($old);

        if ($status != 0) {
            self::fail(implode("\n", $output));
        }
    }

    /**
     * @dataProvider createProvider
     *
     * @param array|string $inputData
     * @param int|float $tolerance
     * @param int $dpi
     */
    public function testCreate($inputData, $tolerance, $dpi = 150)
    {
        $classFile = $this->getClassFile();

        $tmpPath = is_array($inputData)
            ? $inputData['tmpPath']
            : basename($inputData);

        $directory = dirname($classFile) . '/' . pathinfo($classFile, PATHINFO_FILENAME) . '/' . $tmpPath;
        $tmpDir = $directory . '/compare';

        if (!is_dir($tmpDir)) {
            $old = umask(0);
            if (!@mkdir($tmpDir, 0775, true) && !is_dir($tmpDir)) {
                throw new \RuntimeException(sprintf(
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

        $originalDir = $directory . '/original';
        $originalFile = $originalDir . '/result.pdf';
        if (!\is_file($originalFile)) {
            throw new \RuntimeException(\sprintf('Couldn\'t find original file: %s', $originalFile));
        }

        $this->createImage($originalFile, $originalDir, $dpi);
        $this->createImage($outputFile, $tmpDir, $dpi);

        $esc = function ($path) {
            return preg_replace('/([*?\[])/', '[$1]', $path);
        };

        $originalImages = [];
        $testImages = [];
        /** @noinspection LowPerformingFilesystemOperationsInspection */
        foreach (glob($esc($tmpDir) . '/../original/*.png') as $filename) {
            $originalImages[] = $filename;
        }

        $diff = $tmpDir . '/diff.png';
        if (is_file($diff)) {
            unlink($diff);
        }

        /** @noinspection LowPerformingFilesystemOperationsInspection */
        foreach (glob($esc($tmpDir) . '/*.png') as $filename) {
            $testImages[] = $filename;
        }

        self::assertCount(count($originalImages), $testImages, 'Count of pages for file ' . $tmpPath);

        foreach ($originalImages as $k => $filename) {
            $out = exec(sprintf(
                'compare -quiet -alpha on -metric mae "%s" "%s" "%s" 2>&1',
                $originalImages[$k],
                $testImages[$k],
                $diff
            ));

            if (is_file($diff)) {
                $old = umask(0);
                chmod($diff, 0775);
                umask($old);
            }

            $assertMethod = (
                \method_exists($this, 'assertMatchesRegularExpression')
                ? 'assertMatchesRegularExpression'
                : 'assertRegExp'
            );
            $this->$assertMethod(
                '~^[0-9.]*(\s\([0-9e.\-]*\))?$~',
                $out,
                $out . ' for file ' . $tmpPath
            );
            //var_dump($out);
            self::assertLessThan($tolerance, $out, 'Page ' . $filename . ' for file ' . $tmpPath);

            unlink($diff);
        }

        //clean up
        foreach ($testImages as $filename) {
            unlink($filename);
        }
        foreach ($originalImages as $filename) {
            unlink($filename);
        }
        unlink($tmpDir . DIRECTORY_SEPARATOR . 'result.pdf');
        rmdir($tmpDir);
    }
}
