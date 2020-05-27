<?php

declare (strict_types=1);

namespace PhpQrCode;

/**
 * Class Image
 * @package PhpQrCode
 */
class Image
{
    public static function png(array $frame, string $filename = null, int $pixelPerPoint = 4, int $outerFrame = 4, bool $saveAndPrint = false): void
    {
        $image = self::image($frame, $pixelPerPoint, $outerFrame);

        if (empty($filename)) {
            Header("Content-type: image/png");
            ImagePng($image);
        } else {
            if ($saveAndPrint) {
                ImagePng($image, $filename);
                header("Content-type: image/png");
                ImagePng($image);
            } else {
                ImagePng($image, $filename);
            }
        }

        ImageDestroy($image);
    }

    public static function jpg(array $frame, string $filename = null, int $pixelPerPoint = 8, int $outerFrame = 4, int $q = 85): void
    {
        $image = self::image($frame, $pixelPerPoint, $outerFrame);

        if (empty($filename)) {
            Header("Content-type: image/jpeg");
            ImageJpeg($image, null, $q);
        } else {
            ImageJpeg($image, $filename, $q);
        }

        ImageDestroy($image);
    }

    private static function image(array $frame, int $pixelPerPoint = 4, int $outerFrame = 4)
    {
        $h = count($frame);
        $w = strlen($frame[0]);

        $imgW = $w + 2 * $outerFrame;
        $imgH = $h + 2 * $outerFrame;

        $baseImage = ImageCreate($imgW, $imgH);

        $col[0] = ImageColorAllocate($baseImage, 255, 255, 255);
        $col[1] = ImageColorAllocate($baseImage, 0, 0, 0);

        imagefill($baseImage, 0, 0, $col[0]);

        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                if ($frame[$y][$x] == '1') {
                    ImageSetPixel($baseImage, $x + $outerFrame, $y + $outerFrame, $col[1]);
                }
            }
        }

        /** @var resource|false $targetImage */
        $targetImage = ImageCreate($imgW * $pixelPerPoint, $imgH * $pixelPerPoint);
        ImageCopyResized($targetImage, $baseImage, 0, 0, 0, 0, $imgW * $pixelPerPoint, $imgH * $pixelPerPoint, $imgW, $imgH);
        ImageDestroy($baseImage);

        return $targetImage;
    }
}
