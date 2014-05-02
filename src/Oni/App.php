<?php
/**
 * Oni Application
 * 
 * @package     Oni
 * @author      ScarWu
 * @copyright   Copyright (c) 2014, ScarWu (http://scar.simcz.tw/)
 * @link        http://github.com/scarwu/Oni
 */

namespace Oni;

class App
{
    private $set;

    public function __construct()
    {
        $this->set = [
            'name' => 'OniApp',
            'api' => false,
            'api/default' => 'Index',
            'model' => false,
            'template' => false,
            'template/engine' => 'native',
            'static' => false,
            'cache' => false,
            'cache/time' => 300
        ];
    }

    public function set($key, $value)
    {
        $this->set[$key] = $value;

        return $this;
    }

    private function loadStatic()
    {
        $query = $this->query();

        if ('' === $query) {
            return false;
        }

        $path = $this->set['static'] . "/$query";

        if (!file_exists($path)) {
            return false;
        }

        if ('get' !== $this->method()) {
            return false;
        }

        echo file_get_contents($path);

        return true;
    }

    private function loadCache()
    {
        $query = $this->query();
        
        if ('' === $query) {
            return false;
        }

        $query = md5($query);
        $path = $this->set['cache'] . "/$query";

        if (!file_exists($path)) {
            return false;
        }

        if ('get' !== $this->method()) {
            return false;
        }

        echo file_get_contents($path);

        return true;
    }

    private function loadApi()
    {
        $query = explode('/', $this->query());

        if ('' === $query[0]) {
            $query[0] = $this->set['api/default'];
        }

        $api_is_found = false;
        $api_name = ucfirst($this->set['name']) . '\Api';
        $api_path = $this->set['api'];

        while($query) {
            $file_name = ucfirst($query[0]);

            if (!file_exists("$api_path/{$file_name}Api.php")) {
                break;
            }

            $api_is_found = true;
            $api_path = "$api_path/$file_name";
            $api_name .= "\\$file_name";

            array_shift($query);
        }

        if (!$api_is_found) {
            Res::code(404);
            echo '404';
            return false;
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
                'path' => $this->set['template']
            ]);

            if (false !== $api->up()) {
                $method = $this->method() . 'Action';
                $api->$method();
            }

            $api->down();
        }
    }

    public function run()
    {
        if ($this->set['static'] && $this->loadStatic()) {
            return true;
        }

        if ($this->set['cache'] && $this->loadCache()) {
            return true;
        }

        if ($this->set['api'] && $this->loadApi()) {
            return true;
        }

        Res::code(404);
    }

    private function method()
    {
        $method = isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']) 
            ? $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']
            : $_SERVER['REQUEST_METHOD'];

        return strtolower($method);
    }

    private function query()
    {
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
