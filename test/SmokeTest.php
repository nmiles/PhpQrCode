<?php

namespace PhpQrCodeTest;

use PHPUnit\Framework\TestCase;
use PhpQrCode;

class SmokeTest extends TestCase
{
    public function test()
    {
        $pngTmpDir = sys_get_temp_dir() . '/';

        // What to put in the QR
        $data = 'https://github.com/nmiles/PhpQrCode';

        // Override default config if required
        PhpQrCode\Config::configure(['defaultMask' => 3]);
        $this->assertEquals(3, PhpQrCode\Config::$defaultMask);

        $levels = ['L', 'M', 'Q', 'H'];
        foreach ($levels as $errorCorrectionLevel) {
            for ($matrixPointSize = 1; $matrixPointSize <= 10; $matrixPointSize++) {
                $filename = $pngTmpDir . 'test' . md5($data . '|' . $errorCorrectionLevel . '|' . $matrixPointSize) . '.png';
                PhpQrCode\Code::png($data, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
                $this->assertFileExists($filename);
                unlink($filename);
                //echo "Matrix point size: {$matrixPointSize}, error correction level: {$errorCorrectionLevel}, output {$filename}\n";
            }
        }

        // Kanji
        $data = '分分分分国国国';
        $output = PhpQrCode\Code::text($data, false, 'L', 5, 5);
        $this->assertIsArray($output);

        // Change config
        PhpQrCode\Config::configure(['findFromRandom' => true]);
        $this->assertEquals(true, PhpQrCode\Config::$findFromRandom);
        $output = PhpQrCode\Code::text($data, false, 'L', 5, 5);
        $this->assertIsArray($output);

        ob_start();
        PhpQrCode\Tools::timeBenchmark();
        $output = ob_get_clean();

        $this->assertStringContainsString('after_encode', $output);
    }
}
