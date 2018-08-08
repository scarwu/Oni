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
            'namespace' => 'OniApp',
            'controller/namespace' => null,     // Requied
            'controller/path' => null,          // Requied
            'controller/default' => 'Index',
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
        $uri = $this->req->uri();

        if ('' === $uri) {
            return false;
        }

        $path = $this->_attr['static/path'];
        $fullpath = "{$path}/{$uri}";

        if (!file_exists($fullpath)) {
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
        $uri = $this->req->uri();

        if ('' === $uri) {
            return false;
        }

        $hash = md5($uri);
        $path = $this->_attr['cache/path'];
        $fullpath = "{$path}/{$hash}";

        if (!file_exists($fullpath)) {
            return false;
        }

        // Check File Create Time
        if (time() - filectime($fullpath) > $this->_attr['cache/time']) {
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
        $segments = explode('/', $this->req->uri());

        // Set Deafult Task
        if ('' === $segments[0]) {
            $segments[0] = $this->_attr['controller/default'];
        }

        foreach ($segments as $segment) {
            $segment = ucfirst($segment);

            $path = "{$path}/{$segment}";
            $namespace = "{$namespace}\\{$segment}";
        }

        if (false === file_exists("{$path}Controller.php")) {
            throw new Exception("Controller is not found.");
        }

        // New Controller Instance
        $namespace = "{$namespace}Controller";
        $instance = new $namespace($this->req, $this->res);

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
