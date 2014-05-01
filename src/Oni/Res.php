<?php

namespace Oni;

class Res {

	static private $path;

    private function __construct()
    {

    }

    static public function setPath($path)
    {
    	self::$path = $path;
    }

    static public function html($_template, $_data)
    {
    	$_template_path = self::$path . "/$_template.phtml";

        if (file_exists($_template_path)) {
        	foreach ($_data as $_key => $_value) {
	            $$_key = $_value;
	        }

        	include $_template_path;
        }
    }

}