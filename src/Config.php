<?php

namespace PhpQrCode;

/**
 * Class Config
 * @package PhpQrCode
 */

class Config
{
    private static $initialized = false;
    public static $cacheable = false; // use cache - more disk reads but less CPU power, masks and format templates are stored there
    public static $cacheDir; // used when QR_CACHEABLE === true
    public static $logDir; // default error logs dir
    public static $findBestMask = true; // if true, estimates best mask (spec. default, but extremally slow; set to false to significant performance boost but (propably) worst quality code
    public static $findFromRandom = false; // if false, checks all masks available, otherwise value tells count of masks need to be checked, mask id are got randomly
    public static $defaultMask = 2; // when QR_FIND_BEST_MASK === false
    public static $pngMaximumSize = 512; // maximum allowed png image width (in pixels), tune to make sure GD and PHP can handle such big images

    public static function init($config = [])
    {
        static::$cacheDir = dirname(__FILE__).DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR;
        static::$logDir = dirname(__FILE__).DIRECTORY_SEPARATOR;
        foreach ($config as $key => $value) {
            static::$$key = $value;
        }
        static::$initialized = true;
    }
}
