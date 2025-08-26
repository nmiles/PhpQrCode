<?php

declare(strict_types=1);

namespace PhpQrCode;

class Config
{
    // use cache - more disk reads but less CPU power, masks and format templates are stored there
    public static bool $cacheable = false;

    // used when $cacheable === true
    public static string $cacheDir;

    // default error logs dir
    public static string $logDir;

    // if true, estimates best mask (spec. default, but extremely slow; set to false to significant performance boost but (probably) worse quality code
    public static bool $findBestMask = true;

    // if false, checks all masks available, otherwise value tells count of masks need to be checked, mask id are got randomly
    public static bool $findFromRandom = false;

    // when $findBestMask === false
    public static int $defaultMask = 2;

    // maximum allowed png image width (in pixels), tune to make sure GD and PHP can handle such big images
    public static int $pngMaximumSize = 512;

    /**
     * Override default config
     */
    public static function configure(array $config = [])
    {
        static::$cacheDir = __DIR__ . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
        static::$logDir = __DIR__ . DIRECTORY_SEPARATOR;
        foreach ($config as $key => $value) {
            static::${$key} = $value;
        }
    }
}
