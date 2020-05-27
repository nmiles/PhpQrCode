<?php

declare (strict_types=1);

namespace PhpQrCode;

use Exception;

/**
 * Class Split
 * Input splitting classes
 * @package PhpQrCode
 */
class Split
{
    /** @var string */
    public $dataStr = '';
    /** @var Input */
    public $input;
    /** @var int */
    public $modeHint;

    public function __construct(string $dataStr, Input $input, int $modeHint)
    {
        $this->dataStr = $dataStr;
        $this->input = $input;
        $this->modeHint = $modeHint;
    }

    public static function isDigitAt(string $str, int $pos): bool
    {
        if ($pos >= strlen($str))
            return false;

        return ((ord($str[$pos]) >= ord('0')) && (ord($str[$pos]) <= ord('9')));
    }

    public static function isAlNumAt(string $str, int $pos): bool
    {
        if ($pos >= strlen($str))
            return false;

        return (Input::lookAnTable(ord($str[$pos])) >= 0);
    }

    public function identifyMode(int $pos): int
    {
        if ($pos >= strlen($this->dataStr)) {
            return Str::QR_MODE_NUL;
        }

        $c = $this->dataStr[$pos];

        if (self::isDigitAt($this->dataStr, $pos)) {
            return Str::QR_MODE_NUM;
        } else if (self::isAlNumAt($this->dataStr, $pos)) {
            return Str::QR_MODE_AN;
        } else if ($this->modeHint == Str::QR_MODE_KANJI) {
            if ($pos + 1 < strlen($this->dataStr)) {
                $d = $this->dataStr[$pos + 1];
                $word = (ord($c) << 8) | ord($d);
                if (($word >= 0x8140 && $word <= 0x9ffc) || ($word >= 0xe040 && $word <= 0xebbf)) {
                    return Str::QR_MODE_KANJI;
                }
            }
        }

        return Str::QR_MODE_8;
    }

    public function eatNum(): int
    {
        $ln = Spec::lengthIndicator(Str::QR_MODE_NUM, $this->input->getVersion());

        $p = 0;
        while (self::isDigitAt($this->dataStr, $p)) {
            $p++;
        }

        $run = $p;
        $mode = $this->identifyMode($p);

        if ($mode == Str::QR_MODE_8) {
            $dif = Input::estimateBitsModeNum($run) + 4 + $ln
                + Input::estimateBitsMode8(1)         // + 4 + l8
                - Input::estimateBitsMode8($run + 1); // - 4 - l8
            if ($dif > 0) {
                return $this->eat8();
            }
        }
        if ($mode == Str::QR_MODE_AN) {
            $dif = Input::estimateBitsModeNum($run) + 4 + $ln
                + Input::estimateBitsModeAn(1)        // + 4 + la
                - Input::estimateBitsModeAn($run + 1);// - 4 - la
            if ($dif > 0) {
                return $this->eatAn();
            }
        }

        $ret = $this->input->append(Str::QR_MODE_NUM, $run, str_split($this->dataStr));
        if ($ret < 0) {
            return -1;
        }

        return $run;
    }

    public function eatAn(): int
    {
        $la = Spec::lengthIndicator(Str::QR_MODE_AN, $this->input->getVersion());
        $ln = Spec::lengthIndicator(Str::QR_MODE_NUM, $this->input->getVersion());

        $p = 0;

        while (self::isAlNumAt($this->dataStr, $p)) {
            if (self::isDigitAt($this->dataStr, $p)) {
                $q = $p;
                while (self::isDigitAt($this->dataStr, $q)) {
                    $q++;
                }

                $dif = Input::estimateBitsModeAn($p) // + 4 + la
                    + Input::estimateBitsModeNum($q - $p) + 4 + $ln
                    - Input::estimateBitsModeAn($q); // - 4 - la

                if ($dif < 0) {
                    break;
                } else {
                    $p = $q;
                }
            } else {
                $p++;
            }
        }

        $run = $p;

        if (!self::isAlNumAt($this->dataStr, $p)) {
            $dif = Input::estimateBitsModeAn($run) + 4 + $la
                + Input::estimateBitsMode8(1) // + 4 + l8
                - Input::estimateBitsMode8($run + 1); // - 4 - l8
            if ($dif > 0) {
                return $this->eat8();
            }
        }

        $ret = $this->input->append(Str::QR_MODE_AN, $run, str_split($this->dataStr));
        if ($ret < 0) {
            return -1;
        }

        return $run;
    }


    public function eatKanji(): int
    {
        $p = 0;

        while ($this->identifyMode($p) == Str::QR_MODE_KANJI) {
            $p += 2;
        }
        $run = $p;

        $ret = $this->input->append(Str::QR_MODE_KANJI, $p, str_split($this->dataStr));
        if ($ret < 0) {
            return -1;
        }

        return $run;
    }


    public function eat8(): int
    {
        $la = Spec::lengthIndicator(Str::QR_MODE_AN, $this->input->getVersion());
        $ln = Spec::lengthIndicator(Str::QR_MODE_NUM, $this->input->getVersion());

        $p = 1;
        $dataStrLen = strlen($this->dataStr);

        while ($p < $dataStrLen) {
            $mode = $this->identifyMode($p);
            if ($mode == Str::QR_MODE_KANJI) {
                break;
            }
            if ($mode == Str::QR_MODE_NUM) {
                $q = $p;
                while (self::isDigitAt($this->dataStr, $q)) {
                    $q++;
                }
                $dif = Input::estimateBitsMode8($p) // + 4 + l8
                    + Input::estimateBitsModeNum($q - $p) + 4 + $ln
                    - Input::estimateBitsMode8($q); // - 4 - l8
                if ($dif < 0) {
                    break;
                } else {
                    $p = $q;
                }
            } else if ($mode == Str::QR_MODE_AN) {
                $q = $p;
                while (self::isAlNumAt($this->dataStr, $q)) {
                    $q++;
                }
                $dif = Input::estimateBitsMode8($p)  // + 4 + l8
                    + Input::estimateBitsModeAn($q - $p) + 4 + $la
                    - Input::estimateBitsMode8($q); // - 4 - l8
                if ($dif < 0) {
                    break;
                } else {
                    $p = $q;
                }
            } else {
                $p++;
            }
        }

        $run = $p;
        $ret = $this->input->append(Str::QR_MODE_8, $run, str_split($this->dataStr));

        if ($ret < 0) {
            return -1;
        }

        return $run;
    }

    public function splitString(): ?int
    {
        while (strlen($this->dataStr) > 0) {
            if ($this->dataStr == '') {
                return 0;
            }

            $mode = $this->identifyMode(0);

            switch ($mode) {
                case Str::QR_MODE_NUM:
                    $length = $this->eatNum();
                    break;
                case Str::QR_MODE_AN:
                    $length = $this->eatAn();
                    break;
                case Str::QR_MODE_KANJI:
                    $length = $this->eatKanji();
                    break;
                default:
                    $length = $this->eat8();
                    break;

            }

            if ($length == 0) {
                return 0;
            }
            if ($length < 0) {
                return -1;
            }

            $this->dataStr = substr($this->dataStr, $length);
        }
        return null;
    }

    public function toUpper(): string
    {
        $stringLen = strlen($this->dataStr);
        $p = 0;

        while ($p < $stringLen) {
            $mode = self::identifyMode($p);
            //$mode = self::identifyMode(substr($this->dataStr, $p), $this->modeHint);
            if ($mode == Str::QR_MODE_KANJI) {
                $p += 2;
            } else {
                if (ord($this->dataStr[$p]) >= ord('a') && ord($this->dataStr[$p]) <= ord('z')) {
                    $this->dataStr[$p] = chr(ord($this->dataStr[$p]) - 32);
                }
                $p++;
            }
        }

        return $this->dataStr;
    }

    public static function splitStringToQRInput(string $string, Input $input, int $modeHint, bool $caseSensitive = true): ?string
    {
        if (is_null($string) || $string == '\0' || $string == '') {
            throw new Exception('empty string!!!');
        }

        $split = new Split($string, $input, $modeHint);

        if (!$caseSensitive) {
            $split->toUpper();
        }

        return $split->splitString();
    }
}
