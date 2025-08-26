<?php

declare(strict_types=1);

namespace PhpQrCode;

/**
 * Common constants
 */
class Str
{
    // Encoding modes
    public const QR_MODE_NUL = -1;

    public const QR_MODE_NUM = 0;

    public const QR_MODE_AN = 1;

    public const QR_MODE_8 = 2;

    public const QR_MODE_KANJI = 3;

    public const QR_MODE_STRUCTURE = 4;

    // Levels of error correction.
    public const QR_ECLEVEL_L = 0;

    public const QR_ECLEVEL_M = 1;

    public const QR_ECLEVEL_Q = 2;

    public const QR_ECLEVEL_H = 3;

    public static function set(array &$srcTab, int $x, int $y, string $repl, int $replLen = null): void
    {
        $srcTab[$y] = substr_replace(
            $srcTab[$y],
            ($replLen === null ? $repl : substr($repl, 0, $replLen)),
            $x,
            ($replLen ?? strlen($repl))
        );
    }
}
