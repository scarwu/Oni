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
     * @var array
     */
    private static $_attr = [
        'name' => 'OniApp',
        'controller' => false,
        'controller/default' => 'Index',
        'model' => false,
        'view' => false,
        'view/ext' => 'php',
        'static' => false,
        'cache' => false,
        'cache/time' => 300 // 300 sec = 5 min
    ];

    /**
     * @var object
     */
    protected $req = null;

    /**
     * @var object
     */
    protected $res = null;

    /**
     * Set Attr
     *
     * @param string $key
     * @param string $value
     *
     * @return object
     */
    public function setAttr($key, $value)
    {
        self::$_attr[$key] = $value;

        return $this;
    }

    /**
     * Get Attr
     *
     * @param string $key
     *
     * @return object|null
     */
    public function getAttr($key)
    {
        return isset(self::$_attr[$key])
            ? self::$_attr[$key] : null;
    }

    /**
     * Run Application
     *
     * @return bool
     */
    public function run()
    {
        // Initialize Request & Response
        $this->req = Req::init();
        $this->res = Res::init();

        // Set Attrs
        $this->res->setAttr('view', self::$_attr['view']);
        $this->res->setAttr('view/ext', self::$_attr['view/ext']);

        // Load Static File
        if (self::$_attr['static'] && $this->loadStatic()) {
            return true;
        }

        // Load Cache File
        if (self::$_attr['cache'] && $this->loadCache()) {
            return true;
        }

        // Load Controller
        if (self::$_attr['controller'] && $this->loadController()) {
            return true;
        }

        if (200 === http_response_code()) {
            http_response_code(404);
        }
    }

    /**
     * Load Static File
     *
     * @return bool
     */
    private function loadStatic()
    {
        $uri = $this->req->uri();

        if ('' === $uri) {
            return false;
        }

        $prefix = self::$_attr['static'];
        $path = "{$prefix}/{$uri}";

        if (!file_exists($path)) {
            return false;
        }

        if ('get' !== $this->req->method()) {
            return false;
        }

        $mime = mime_content_type($path);

        header("Content-Type: {$mime}");
        echo file_get_contents($path);

        return true;
    }

    /**
     * Load Cache File
     *
     * @return bool
     */
    private function loadCache()
    {
        $uri = $this->req->uri();

        if ('' === $uri) {
            return false;
        }

        $hash = md5($uri);
        $prefix = self::$_attr['cache'];
        $path = "{$prefix}/{$hash}";

        if (!file_exists($path)) {
            return false;
        }

        // Check File Create Time
        if (time() - filectime($path) > self::$_attr['cache/time']) {
            unlink($path);

            return false;
        }

        if ('get' !== $this->req->method()) {
            return false;
        }

        $mime = mime_content_type($path);

        header("Content-Type: {$mime}");

        echo file_get_contents($path);

        return true;
    }

    /**
     * Load Controller
     *
     * @return bool
     */
    private function loadController()
    {
        $uri = explode('/', $this->req->uri());

        // Set Deafult Controller
        if ('' === $uri[0]) {
            $uri[0] = self::$_attr['controller/default'];
        }

        $name = ucfirst(self::$_attr['name']) . '\Controller';
        $prefix = self::$_attr['controller'];

        $uri_temp = $uri;
        $name_temp = $name;
        $prefix_temp = $prefix;

        $is_found = false;

        // Search Controller
        while ($uri) {
            $file_name = ucfirst($uri[0]);

            if (file_exists("{$prefix_temp}/{$file_name}Controller.php")) {
                array_shift($uri);

                $name = "{$name_temp}\\{$file_name}";
                $prefix = "{$prefix_temp}/{$file_name}";

                $uri_temp = $uri;
                $name_temp = $name;
                $prefix_temp = $prefix;

                $is_found = true;
            } elseif (file_exists("{$prefix_temp}/{$file_name}")) {
                array_shift($uri);

                $uri_temp = $uri;
                $name_temp = "{$name_temp}\\{$file_name}";
                $prefix_temp = "{$prefix_temp}/{$file_name}";
            } else {
                break;
            }
        }

        // Response HTTP Status Code 404
        if (!$is_found) {
            http_response_code(404);

            return false;
        }

        // Require Controller
        require "{$prefix}Controller.php";

        // New Controller Instance
        $controller_name = "{$name}Controller";
        $instance = new $controller_name($this->req, $this->res);

        $method = $this->req->method();
        $action_name = "{$method}Action";

        if (method_exists($instance, $action_name)) {

            // Call Function: up -> xxxAction -> down
            if (false !== $instance->up()) {
                $instance->$action_name();
            }

            $instance->down();

            return true;
        }

        http_response_code(501);

        return false;
    }
}
