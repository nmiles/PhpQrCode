<?php

declare (strict_types=1);

namespace PhpQrCode;

use Exception;

/**
 * Class Code
 * One of the main encoder classes
 * @package PhpQrCode
 */
class Code
{
    public $version;
    public $width;
    public $data;

    public function encodeMask(Input $input, $mask)
    {
        if ($input->getVersion() < 0 || $input->getVersion() > Spec::QRSPEC_VERSION_MAX) {
            throw new Exception('wrong version');
        }
        if ($input->getErrorCorrectionLevel() > Str::QR_ECLEVEL_H) {
            throw new Exception('wrong level');
        }

        $raw = new RawCode($input);

        Tools::markTime('after_raw');

        $version = $raw->version;
        $width = Spec::getWidth($version);
        $frame = Spec::newFrame($version);

        $filler = new FrameFiller($width, $frame);
        if (is_null($filler)) {
            return null;
        }

        // interleaved data and ecc codes
        for ($i = 0; $i < $raw->dataLength + $raw->eccLength; $i++) {
            $code = $raw->getCode();
            $bit = 0x80;
            for ($j = 0; $j < 8; $j++) {
                $addr = $filler->next();
                $filler->setFrameAt($addr, 0x02 | (($bit & $code) != 0));
                $bit = $bit >> 1;
            }
        }

        Tools::markTime('after_filler');

        unset($raw);

        // remainder bits
        $j = Spec::getRemainder($version);
        for ($i = 0; $i < $j; $i++) {
            $addr = $filler->next();
            $filler->setFrameAt($addr, 0x02);
        }

        $frame = $filler->frame;
        unset($filler);

        // masking
        $maskObj = new Mask();
        if ($mask < 0) {
            if (Config::$findBestMask) {
                $masked = $maskObj->mask($width, $frame, $input->getErrorCorrectionLevel());
            } else {
                $masked = $maskObj->makeMask($width, $frame, (intval(Config::$defaultMask) % 8), $input->getErrorCorrectionLevel());
            }
        } else {
            $masked = $maskObj->makeMask($width, $frame, $mask, $input->getErrorCorrectionLevel());
        }

        if (is_null($masked)) {
            return null;
        }

        Tools::markTime('after_mask');

        $this->version = $version;
        $this->width = $width;
        $this->data = $masked;

        return $this;
    }

    public function encodeInput(Input $input)
    {
        return $this->encodeMask($input, -1);
    }

    public function encodeString8bit(string $string, $version, $level)
    {
        if (is_null($string)) {
            throw new Exception('empty string!');
        }

        $input = new Input($version, $level);
        if (empty($input)) {
            return null;
        }

        $ret = $input->append(Str::QR_MODE_8, strlen($string), str_split($string));
        if ($ret < 0) {
            unset($input);
            return null;
        }
        return $this->encodeInput($input);
    }

    public function encodeString(string $string, $version, $level, $hint, $casesensitive)
    {
        if ($hint != Str::QR_MODE_8 && $hint != Str::QR_MODE_KANJI) {
            throw new Exception('bad hint');
        }

        $input = new Input($version, $level);
        if (is_null($input)) {
            return null;
        }

        $ret = Split::splitStringToQRInput($string, $input, $hint, $casesensitive);
        if ($ret < 0) {
            return null;
        }

        return $this->encodeInput($input);
    }

    public static function png(string $text, string $outfile = null, $level = Str::QR_ECLEVEL_L, int $size = 3, int $margin = 4, bool $saveAndPrint = false)
    {
        $enc = Encode::factory($level, $size, $margin);
        return $enc->encodePNG($text, $outfile, $saveAndPrint);
    }

    public static function text(string $text, string $outfile = null, $level = Str::QR_ECLEVEL_L, int $size = 3, int $margin = 4)
    {
        $enc = Encode::factory($level, $size, $margin);
        return $enc->encode($text, $outfile);
    }

    public static function raw(string $text, string $outfile = null, $level = Str::QR_ECLEVEL_L, int $size = 3, int $margin = 4)
    {
        $enc = Encode::factory($level, $size, $margin);
        return $enc->encodeRAW($text);
    }
}
