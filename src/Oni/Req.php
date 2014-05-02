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
    static private $req;

    private function __construct()
    {
        // nothing here
    }

    /**
     * Initialize Request Module
     *
     * @param Array
     */
    static function init($req)
    {
        self::$req = $req;
    }

    /**
     * Get Request Method
     *
     * @return String
     */
    static public function method()
    {
        return self::$req['method'];
    }

    /**
     * Get Request Query String
     *
     * @return String
     */
    static public function query()
    {
        return self::$req['query'];
    }
}
