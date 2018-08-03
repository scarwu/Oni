<?php
/**
 * Oni Application
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace Oni\Web;

use Oni\Web\Req;
use Oni\Web\Res;

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
            'controller' => false,
            'controller/default' => 'Index',
            'model' => false,
            'view' => false,
            'view/engine' => 'native',
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
     * Run Application
     */
    public function run()
    {
        // Load Static File
        if ($this->set['static'] && $this->loadStatic()) {
            return true;
        }

        // Load Cache File
        if ($this->set['cache'] && $this->loadCache()) {
            return true;
        }

        // Load Controller
        if ($this->set['controller'] && $this->loadController()) {
            return true;
        }

        if (200 === http_response_code()) {
            http_response_code(404);
        }
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

        $path = $this->set['static'] . "/{$param}";

        if (!file_exists($path)) {
            return false;
        }

        if ('get' !== $this->method()) {
            return false;
        }

        $mime = mime_content_type($path);

        header("Content-Type: {$mime}");
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
        $path = $this->set['cache'] . "/{$param}";

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

        header("Content-Type: {$mime}");

        echo file_get_contents($path);

        return true;
    }

    /**
     * Load Controller
     */
    private function loadController()
    {
        $param = explode('/', $this->param());

        // Set Deafult Controller
        if ('' === $param[0]) {
            $param[0] = $this->set['controller/default'];
        }

        $controller_param_temp = $param;
        $controller_name_temp = ucfirst($this->set['name']) . '\Controller';
        $controller_path_temp = $this->set['controller'];

        $controller_is_found = false;

        $controller_param = $controller_param_temp;
        $controller_name = $controller_name_temp;
        $controller_path = $controller_path_temp;

        // Search Controller
        while ($param) {
            $file_name = ucfirst($param[0]);

            if (file_exists("{$controller_path_temp}/{$file_name}Controller.php")) {
                array_shift($param);

                $controller_param_temp = $param;
                $controller_name_temp = "{$controller_name_temp}\\{$file_name}";
                $controller_path_temp = "{$controller_path_temp}/{$file_name}";

                $controller_is_found = true;

                $controller_param = $controller_param_temp;
                $controller_name = $controller_name_temp;
                $controller_path = $controller_path_temp;
            } elseif (file_exists("{$controller_path_temp}/{$file_name}")) {
                array_shift($param);

                $controller_param_temp = $param;
                $controller_name_temp = "{$controller_name_temp}\\{$file_name}";
                $controller_path_temp = "{$controller_path_temp}/{$file_name}";
            } else {
                break;
            }
        }

        // Response HTTP Status Code 404
        if (!$controller_is_found) {
            http_response_code(404);

            return false;
        }

        // Require Controller
        require "{$controller_path}Controller.php";

        // New Controller Instance
        $controller_name .= 'Controller';
        $controller = new $controller_name();
        $method = $this->method();

        if (method_exists($controller, "{$method}Action")) {
            // Initialize Request Module
            Req::init([
                'method' => $method,
                'param' => $controller_param
            ]);

            // Initialize Response Module
            Res::init([
                'path' => $this->set['view']
            ]);

            // Call Function: up -> xxxAction -> down
            if (false !== $controller->up()) {
                $action_name = "{$method}Action";
                $controller->$action_name();
            }

            $controller->down();

            return true;
        }

        http_response_code(501);

        return false;
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

        if (isset($_SERVER['PATH_INFO'])) {
            $param = $_SERVER['PATH_INFO'];
        } elseif (isset($_SERVER['REQUEST_URI']) || isset($_SERVER['PHP_SELF'])) {
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
