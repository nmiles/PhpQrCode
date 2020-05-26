<?php

namespace PhpQrCodeTest;

use PHPUnit\Framework\TestCase;
use PhpQrCode;

class SmokeTest extends TestCase
{
    public function test()
    {
        $pngTmpDir = sys_get_temp_dir() . '/';

        $levels = ['L', 'M', 'Q', 'H'];

        $data = 'https://www.google.com';

        foreach ($levels as $errorCorrectionLevel) {
            for ($matrixPointSize = 1; $matrixPointSize <= 10; $matrixPointSize++) {
                $filename = $pngTmpDir . 'test' . md5($data . '|' . $errorCorrectionLevel . '|' . $matrixPointSize) . '.png';
                PhpQrCode\Code::png($data, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
                $this->assertFileExists($filename);
                unlink($filename);
                //echo "Matrix point size: {$matrixPointSize}, error correction level: {$errorCorrectionLevel}, output {$filename}\n";
            }
        }

        ob_start();
        PhpQrCode\Tools::timeBenchmark();
        $output = ob_get_clean();

        $this->assertStringContainsString('after_encode', $output);
    }
}
