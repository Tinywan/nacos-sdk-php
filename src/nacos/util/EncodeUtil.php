<?php


namespace nacos\util;


/**
 * Class EncodeUtil
 * @package nacos\util
 */
class EncodeUtil
{
    public static function twoEncode()
    {
        return pack("C*", 2);
    }

    public static function oneEncode()
    {
        return pack("C*", 1);
    }
}