<?php
/**
 * Web Application
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace Oni\Web;

use Exception;
use Oni\Basic;
use Oni\Loader;
use Oni\Web\Req;
use Oni\Web\Res;

class App extends Basic
{
    /**
     * @var object
     */
    protected $req = null;

    /**
     * @var object
     */
    protected $res = null;

    /**
     * Construct
     */
    public function __construct()
    {
        // Set Default Attributes
        $this->_attr = [
            'controller/namespace' => null,     // Requied
            'controller/path' => null,          // Requied
            'controller/default' => 'Main',
            'controller/default/action' => 'default',
            'controller/error' => 'Main',
            'controller/error/action' => 'error',
            'model/namespace' => null,          // Requied
            'model/path' => null,               // Requied
            'view/path' => null,                // Requied
            'view/ext' => 'php',
            'static/path' => null,              // Requied
            'cache/path' => null,               // Requied
            'cache/time' => 300                 // 300 sec = 5 min
        ];

        $this->req = Req::init();
        $this->res = Res::init();
    }

    /**
     * Run Application
     *
     * @return bool
     */
    public function run()
    {
        // Register Controller Classes
        $namespace = $this->getAttr('controller/namespace');
        $path = $this->getAttr('controller/path');

        if (null !== $namespace && null !== $path) {
            Loader::append($namespace, $path);
        }

        // Register Model Classes
        $namespace = $this->getAttr('model/namespace');
        $path = $this->getAttr('model/path');

        if (null !== $namespace && null !== $path) {
            Loader::append($namespace, $path);
        }

        // Set Response Attrs
        $this->res->setAttr('view/path', $this->getAttr('view/path'));
        $this->res->setAttr('view/ext', $this->getAttr('view/ext'));

        // Load Static File
        if (null !== $this->getAttr('static/path')
            && $this->loadStatic()) {

            return true;
        }

        // Load Cache File
        if (null !== $this->getAttr('cache/path')
            && $this->loadCache()) {

            return true;
        }

        // Load Controller
        if (null !== $this->getAttr('controller/namespace')
            && null !== $this->getAttr('controller/path')
            && $this->loadController()) {

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
        $path = $this->getAttr('static/path');
        $uri = $this->req->uri();

        if (false !== is_string($path)
            || '' === $uri) {

            return false;
        }

        $fullpath = "{$path}/{$uri}";

        if (false === file_exists($fullpath)) {
            return false;
        }

        if ('get' !== $this->req->method()) {
            return false;
        }

        $mime = mime_content_type($fullpath);

        header("Content-Type: {$mime}");

        echo file_get_contents($fullpath);

        return true;
    }

    /**
     * Load Cache File
     *
     * @return bool
     */
    private function loadCache()
    {
        $path = $this->getAttr('cache/path');
        $uri = $this->req->uri();

        if (false !== is_string($path)
            || '' === $uri) {

            return false;
        }

        $fullpath = "{$path}/" . md5($uri);

        if (false === file_exists($fullpath)) {
            return false;
        }

        // Check File Create Time
        if (time() - filectime($fullpath) > $this->getAttr('cache/time')) {
            unlink($fullpath);

            return false;
        }

        if ('get' !== $this->req->method()) {
            return false;
        }

        $mime = mime_content_type($fullpath);

        header("Content-Type: {$mime}");

        echo file_get_contents($fullpath);

        return true;
    }

    /**
     * Load Controller
     *
     * @return bool
     */
    private function loadController()
    {
        $namespace = $this->getAttr('controller/namespace');
        $path = $this->getAttr('controller/path');
        $params = explode('/', $this->req->uri());
        $params = '' !== $params[0] ? $params : [];

        foreach ($params as $param) {
            $param = $params[0];
            $param = ucfirst($param);

            if (false === file_exists("{$path}/{$param}")
                && false === file_exists("{$path}/{$param}Controller.php")) {

                break;
            }

            $path = "{$path}/{$param}";
            $namespace = "{$namespace}\\{$param}";

            array_shift($params);
        }

        // Rewrite Controller
        if (false === file_exists("{$path}Controller.php")) {
            $controller = $this->getAttr('controller/default');
            $controller = ucfirst($controller);

            if (false === file_exists("{$path}/{$controller}Controller.php")) {
                $controller = $this->getAttr('controller/error');
                $controller = ucfirst($controller);

                if (false === file_exists("{$path}/{$controller}Controller.php")) {
                    http_response_code(400);

                    return false;
                }
            }

            $namespace = "{$namespace}\\{$controller}";
        }

        // New Controller Instance
        $namespace = "{$namespace}Controller";
        $instance = new $namespace($this->req, $this->res);

        switch ($instance->getAttr('mode')) {
        case 'page':
            if (0 === count($params)) {
                $action_name = $this->getAttr('controller/default/action') . 'Action';
            } else {
                $action_name = array_shift($params) . 'Action';
            }

            break;
        case 'rest':
            $action_name = $this->req->method() . 'Action';

            if (false === method_exists($instance, $action_name)) {
                http_response_code(501);

                return false;
            }

            break;
        default:
            return false;
        }

        // Call Function: up -> xxxAction -> down
        if (false !== $instance->up()) {
            $instance->$action_name($params);
        }

        $instance->down();

        return true;
    }
}
