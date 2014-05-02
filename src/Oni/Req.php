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
    static private $req;

    private function __construct()
    {
        // nothing here
    }

    static function init($req)
    {
        self::$req = $req;
    }

    static public function method()
    {
        return self::$req['method'];
    }

    static public function query()
    {
        return self::$req['query'];
    }
}
