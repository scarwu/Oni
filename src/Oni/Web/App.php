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
    public function __construct() {
        $this->_attr = [
            'name' => 'OniApp',
            'controller' => null,   // Requied
            'controller/default' => 'Index',
            'model' => null,        // Requied
            'view' => null,         // Requied
            'view/ext' => 'php',
            'static' => null,       // Requied
            'cache' => null,        // Requied
            'cache/time' => 300 // 300 sec = 5 min
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
        // Set Response Attrs
        $this->res->setAttr('view', $this->_attr['view']);
        $this->res->setAttr('view/ext', $this->_attr['view/ext']);

        // Load Static File
        if ($this->_attr['static'] && $this->loadStatic()) {
            return true;
        }

        // Load Cache File
        if ($this->_attr['cache'] && $this->loadCache()) {
            return true;
        }

        // Load Controller
        if ($this->_attr['controller'] && $this->loadController()) {
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

        $prefix = $this->_attr['static'];
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
        $prefix = $this->_attr['cache'];
        $path = "{$prefix}/{$hash}";

        if (!file_exists($path)) {
            return false;
        }

        // Check File Create Time
        if (time() - filectime($path) > $this->_attr['cache/time']) {
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
        $current_path = $this->getAttr('controller');
        $current_namespace = $this->getAttr('name') . '\\Controller';

        $segments = explode('/', $this->req->uri());

        // Set Deafult Task
        if ('' === $segments[0]) {
            $segments[0] = $this->_attr['controller/default'];
        }

        foreach ($segments as $segment) {
            $segment = ucfirst($segment);

            $current_path = "{$current_path}/{$segment}";
            $current_namespace = "{$current_namespace}\\{$segment}";
        }

        if (false === file_exists("{$current_path}Controller.php")) {
            throw new Exception("Controller is not found.");
        }

        // Require Controller
        require "{$current_path}Controller.php";

        // New Controller Instance
        $current_namespace = "{$current_namespace}Controller";
        $instance = new $current_namespace($this->req, $this->res);

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
