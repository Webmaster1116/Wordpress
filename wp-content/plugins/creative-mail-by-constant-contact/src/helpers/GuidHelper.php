<?php

namespace CreativeMail\Helpers;

/**
 * Class GuidHelper
 *
 * @package CreativeMail\Helpers
 */
class GuidHelper
{

    public static function generate_guid()
    {

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }

}
