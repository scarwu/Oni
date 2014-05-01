<?php

namespace Oni;

class Res
{
    static private $res;

    private function __construct()
    {
        // nothing here
    }

    static function init($res)
    {
        self::$res = $res;
    }

    static public function html($_template, $_data)
    {
        $_template_path = self::$res['path'] . "/$_template.phtml";

        if (file_exists($_template_path)) {
            foreach ($_data as $_key => $_value) {
                $$_key = $_value;
            }

            include $_template_path;
        }
    }

}