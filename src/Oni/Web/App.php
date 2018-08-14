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
            'controller/default/handler' => 'Main',
            'controller/default/action' => 'default',
            'controller/error/handler' => 'Main',
            'controller/error/action' => 'error',
            'model/namespace' => null,          // Requied
            'model/path' => null,               // Requied
            'view/path' => null,                // Requied
            'view/ext' => 'php',
            'static/path' => null,              // Requied
            'cache/path' => null,               // Requied
            'cache/time' => 300                 // 300 sec = 5 min
        ];

        // Set Instances
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
        // Load Static File
        if (null !== $this->getAttr('static/path') && $this->loadStatic()) {
            return true;
        }

        // Load Cache File
        if (null !== $this->getAttr('cache/path') && $this->loadCache()) {
            return true;
        }

        // Set Response Attrs
        $this->res->setAttr('view/path', $this->getAttr('view/path'));
        $this->res->setAttr('view/ext', $this->getAttr('view/ext'));

        // Register Model Classes
        $namespace = $this->getAttr('model/namespace');
        $path = $this->getAttr('model/path');

        if (null !== $namespace && null !== $path) {
            Loader::append($namespace, $path);
        }

        // Register Controller Classes & Load
        $namespace = $this->getAttr('controller/namespace');
        $path = $this->getAttr('controller/path');

        if (null !== $namespace && null !== $path) {
            Loader::append($namespace, $path);

            if ($this->loadController()) {
                return true;
            }
        }

        if (200 === http_response_code()) {
            http_response_code(404);
        }

        return false;
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

        if (false === is_string($path) || '' === $uri) {
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

        if (false === is_string($path) || '' === $uri) {
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
            $namespace = $this->getAttr('controller/namespace');
            $path = $this->getAttr('controller/path');
            $handler = ucfirst($this->getAttr('controller/default/handler'));
            $action = $this->getAttr('controller/default/action') . 'Action';

            if (false === file_exists("{$path}/{$handler}Controller.php")) {
                $handler = ucfirst($this->getAttr('controller/error/handler'));
                $action = $this->getAttr('controller/error/action') . 'Action';

                if (false === file_exists("{$path}/{$handler}Controller.php")) {
                    http_response_code(400);

                    return false;
                }
            }

            $namespace = "{$namespace}\\{$handler}Controller";
        } else {
            $namespace = "{$namespace}Controller";
            $action = null;
        }

        $instance = new $namespace($this->req, $this->res);

        switch ($instance->getAttr('mode')) {
        case 'page':
            if (null === $action) {
                if (0 === count($params)) {
                    $action = $this->getAttr('controller/default/action') . 'Action';
                } else {
                    $action = array_shift($params) . 'Action';
                }
            }

            if (false === method_exists($instance, $action)) {
                $namespace = $this->getAttr('controller/namespace');
                $path = $this->getAttr('controller/path');

                $handler = $this->getAttr('controller/error/handler');
                $handler = ucfirst($handler);

                if (false === file_exists("{$path}/{$handler}Controller.php")) {
                    http_response_code(400);

                    return false;
                }

                $namespace = "{$namespace}\\{$handler}Controller";
                $instance = new $namespace($this->req, $this->res);
                $action = $this->getAttr('controller/error/action') . 'Action';

                if (false === method_exists($instance, $action)) {
                    http_response_code(400);

                    return false;
                }
            }

            break;
        case 'rest':
            $action = $this->req->method() . 'Action';

            if (false === method_exists($instance, $action)) {
                http_response_code(501);

                return false;
            }

            break;
        default:
            return false;
        }

        // Call Function: up -> xxxAction -> down
        if (false !== $instance->up()) {
            $instance->$action($params);
        }

        $instance->down();

        return true;
    }
}
