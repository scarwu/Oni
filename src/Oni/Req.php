<?php

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