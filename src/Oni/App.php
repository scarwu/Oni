<?php

namespace Oni;

class App
{
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
        $query = $this->query();

        if ('' === $query[0]) {
            $query[0] = $this->default_api;
        }

        $api_name = ucfirst($this->name) . '\Api';
        $api_path = $this->path['api'];

        while($query) {
            $file_name = ucfirst($query[0]);

            if (!file_exists("$api_path/{$file_name}Api.php")) {
                break;
            }

            $api_path = "$api_path/$file_name";
            $api_name .= "\\$file_name";

            array_shift($query);
        }

        require $api_path . 'Api.php';

        $api_name .= 'Api';
        $api = new $api_name();

        if (method_exists($api, $this->method() . 'Action')) {

            Req::init([
                'method' => $this->method(),
                'query' => $query
            ]);

            Res::init([
                'path' => $this->path['template']
            ]);

            $method = $this->method() . 'Action';
            $api->$method();
        }
    }

    private function method()
    {
        $method = isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']) 
            ? $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']
            : $_SERVER['REQUEST_METHOD'];

        return strtolower($method);
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

        return explode('/', trim($query, '/'));
    }

}