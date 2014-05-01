<?php

namespace Oni;

class App {

    private $name;
    private $path;
    private $default_api;

    public function __construct()
    {
        error_reporting(0);

        $this->path = [];
        $this->default_api = 'Index';
    }

    public function setDev($isDev = false)
    {
        if ($isDev) {
            error_reporting(E_ALL | E_STRICT);
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
        }

        return $this;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function setPath($path = [])
    {
        $this->path = $path;

        return $this;
    }

    public function run()
    {
        Res::setPath($this->path['template']);

        $query = explode('/', $this->query());

        if ('' === $query[0]) {
            $query[0] = $this->default_api;
        }

        $api_name = ucfirst($this->name) . '\Api';
        $api_path = $this->path['api'];

        while($query) {
            if (!file_exists("$api_path/{$query[0]}Api.php")) {
                break;
            }

            $api_path = "$api_path/{$query[0]}";
            $api_name .= '\\' . ucfirst(array_pop($query));
        }

        require $api_path . 'Api.php';

        $api_name .= 'Api';
        $api = new $api_name();

        if (method_exists($api, $this->method() . 'Action')) {
            $method = $this->method() . 'Action';
            $api->$method();
        }
    }

    private function method()
    {
        return isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']) 
            ? $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']
            : $_SERVER['REQUEST_METHOD'];
    }

    private function query() {
        $query = null;

        if(!isset($_SERVER['QUERY_STRING'])) {
            $query = $_SERVER['QUERY_STRING'];
        } elseif(isset($_SERVER['REQUEST_URI'])) {
            $pattern = str_replace('/', '\/', $_SERVER['PHP_SELF']);
            $pattern = "/^$pattern\?/";

            $query = urldecode($_SERVER['REQUEST_URI']);
            $query = $query !== preg_replace($pattern, '', $query)
                ? preg_replace($pattern, '', $query)
                : '';
        }

        return trim($query, '/');
    }

}