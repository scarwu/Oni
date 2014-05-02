<?php
/**
 * Oni Request Module
 *
 * @package     Oni
 * @author      ScarWu
 * @copyright   Copyright (c) 2014, ScarWu (http://scar.simcz.tw/)
 * @link        http://github.com/scarwu/Oni
 */

namespace Oni;

class Req
{
    /**
     * @var Array
     */
    private static $req;

    private function __construct()
    {
        // nothing here
    }

    /**
     * Initialize Request Module
     *
     * @param Array
     */
    public static function init($req)
    {
        self::$req = $req;
    }

    /**
     * Get Request Method
     *
     * @return String
     */
    public static function method()
    {
        return self::$req['method'];
    }

    /**
     * Get Request Parameter
     *
     * @return String
     */
    public static function param()
    {
        return self::$req['param'];
    }
}
