<?php

declare (strict_types=1);

namespace PhpQrCode;

/**
 * Class Str
 * Common constants
 * @package PhpQrCode
 */
class Str
{
    // Encoding modes
    const QR_MODE_NUL = -1;
    const QR_MODE_NUM = 0;
    const QR_MODE_AN = 1;
    const QR_MODE_8 = 2;
    const QR_MODE_KANJI = 3;
    const QR_MODE_STRUCTURE = 4;

    // Levels of error correction.
    const QR_ECLEVEL_L = 0;
    const QR_ECLEVEL_M = 1;
    const QR_ECLEVEL_Q = 2;
    const QR_ECLEVEL_H = 3;

    public static function set(array &$srcTab, int $x, int $y, string $repl, int $replLen = null): void
    {
        $srcTab[$y] = substr_replace($srcTab[$y], (is_null($replLen) ? $repl : substr($repl, 0, $replLen)), $x, (is_null($replLen) ? strlen($repl) : $replLen));
    }
}
