<?php

declare (strict_types=1);

namespace PhpQrCode;

use Exception;

/**
 * Class Encode
 * One of the main encoder classes
 * @package PhpQrCode
 */
class Encode
{
    public $caseSensitive = true;
    public $eightBit = false;

    public $version = 0;
    public $size = 3;
    public $margin = 4;

    public $structured = 0; // not supported yet

    public $level = Str::QR_ECLEVEL_L;
    public $hint = Str::QR_MODE_8;

    public static function factory($level = Str::QR_ECLEVEL_L, int $size = 3, int $margin = 4): Encode
    {
        $enc = new Encode();
        $enc->size = $size;
        $enc->margin = $margin;

        switch ($level . '') {
            case '0':
            case '1':
            case '2':
            case '3':
                $enc->level = $level;
                break;
            case 'l':
            case 'L':
                $enc->level = Str::QR_ECLEVEL_L;
                break;
            case 'm':
            case 'M':
                $enc->level = Str::QR_ECLEVEL_M;
                break;
            case 'q':
            case 'Q':
                $enc->level = Str::QR_ECLEVEL_Q;
                break;
            case 'h':
            case 'H':
                $enc->level = Str::QR_ECLEVEL_H;
                break;
        }

        return $enc;
    }

    public function encodeRAW(string $inText): array
    {
        $code = new Code();

        if ($this->eightBit) {
            $code->encodeString8bit($inText, $this->version, $this->level);
        } else {
            $code->encodeString($inText, $this->version, $this->level, $this->hint, $this->caseSensitive);
        }

        return $code->data;
    }

    public function encode(string $inText, string $outfile = null)
    {
        $code = new Code();

        if ($this->eightBit) {
            $code->encodeString8bit($inText, $this->version, $this->level);
        } else {
            $code->encodeString($inText, $this->version, $this->level, $this->hint, $this->caseSensitive);
        }

        Tools::markTime('after_encode');

        if (!empty($outfile)) {
            file_put_contents($outfile, join("\n", Tools::binarize($code->data)));
        } else {
            return Tools::binarize($code->data);
        }
    }

    public function encodePNG(string $inText, string $outfile, $saveAndPrint = false)
    {
        try {
            ob_start();
            $tab = $this->encode($inText);
            $err = ob_get_contents();
            ob_end_clean();

            if ($err != '') {
                Tools::log($outfile, $err);
            }

            $maxSize = (int)(Config::$pngMaximumSize / (count($tab) + 2 * $this->margin));

            Image::png($tab, $outfile, min(max(1, $this->size), $maxSize), $this->margin, $saveAndPrint);

        } catch (Exception $e) {
            Tools::log($outfile, $e->getMessage());
        }
        return $outfile;
    }
}
