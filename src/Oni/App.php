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
    /**
     * @var Array
     */
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
            'cache/time' => 300 // 300 sec = 5 min
        ];
    }

    /**
     * Set Config
     *
     * @param String
     * @param String
     * @return Object
     */
    public function set($key, $value)
    {
        $this->set[$key] = $value;

        return $this;
    }

    /**
     * Load Static File
     */
    private function loadStatic()
    {
        $param = $this->param();

        if ('' === $param) {
            return false;
        }

        $path = $this->set['static'] . "/$param";

        if (!file_exists($path)) {
            return false;
        }

        if ('get' !== $this->method()) {
            return false;
        }

        $mime = mime_content_type($path);

        header("Content-Type: $mime");
        echo file_get_contents($path);

        return true;
    }

    /**
     * Load Cache File
     */
    private function loadCache()
    {
        $param = $this->param();
        
        if ('' === $param) {
            return false;
        }

        $param = md5($param);
        $path = $this->set['cache'] . "/$param";

        if (!file_exists($path)) {
            return false;
        }

        // Check File Create Time
        if (time() - filectime($path) > $this->set['cache/time']) {
            unlink($path);

            return false;
        }

        if ('get' !== $this->method()) {
            return false;
        }

        $mime = mime_content_type($path);

        header("Content-Type: $mime");
        echo file_get_contents($path);

        return true;
    }

    /**
     * Load API Handler
     */
    private function loadApi()
    {
        $param = explode('/', $this->param());

        // Set Deafult API
        if ('' === $param[0]) {
            $param[0] = $this->set['api/default'];
        }

        $api_is_found = false;
        $api_name = ucfirst($this->set['name']) . '\Api';
        $api_path = $this->set['api'];

        // Search API Handler
        while($param) {
            $file_name = ucfirst($param[0]);

            if (!file_exists("$api_path/{$file_name}Api.php")) {
                break;
            }

            $api_is_found = true;
            $api_path = "$api_path/$file_name";
            $api_name .= "\\$file_name";

            array_shift($param);
        }

        // Response HTTP Status Code 404
        if (!$api_is_found) {
            http_response_code(404);

            return false;
        }

        // Require API Handler
        require $api_path . 'Api.php';

        // New API Instance
        $api_name .= 'Api';
        $api = new $api_name();

        if (method_exists($api, $this->method() . 'Action')) {
            // Initialize Request Module
            Req::init([
                'method' => $this->method(),
                'param' => $param
            ]);

            // Initialize Response Module
            Res::init([
                'path' => $this->set['template']
            ]);

            // Call Function: up -> xxxAction -> down
            if (false !== $api->up()) {
                $method = $this->method() . 'Action';
                $api->$method();
            }

            $api->down();

            return true;
        }

        return false;
    }

    /**
     * Run Application
     */
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

        http_response_code(404);
    }

    /**
     * Get Request Method
     *
     * @return String
     */
    private function method()
    {
        $method = isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']) 
            ? $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']
            : $_SERVER['REQUEST_METHOD'];

        return strtolower($method);
    }

    /**
     * Get Request Parameter
     *
     * @return String
     */
    private function param()
    {
        $param = null;

        if(isset($_SERVER['PATH_INFO'])) {
            $param = $_SERVER['PATH_INFO'];
        } elseif(isset($_SERVER['REQUEST_URI']) || isset($_SERVER['PHP_SELF'])) {
            $pattern = str_replace('/', '\/', $_SERVER['SCRIPT_NAME']);
            $pattern = "/^$pattern/";

            $param = isset($_SERVER['REQUEST_URI'])
                ? urldecode($_SERVER['REQUEST_URI'])
                : $_SERVER['PHP_SELF'];

            $param = $param !== preg_replace($pattern, '', $param)
                ? preg_replace($pattern, '', $param)
                : '';
        }

        return trim($param, '/');
    }
}
