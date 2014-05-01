<?php

namespace Oni;

class Res {

    private function __construct();

    static public funtcion getMethod()
    {
        return isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']) 
            ? $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']
            : $_SERVER['REQUEST_METHOD'];
    }

    static private function render($template, $data)
    {
        
    }

}