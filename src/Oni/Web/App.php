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

use Oni\Core\Basic;
use Oni\Core\Loader;
use Oni\Web\Http\Req;
use Oni\Web\Http\Res;
use Oni\Web\View;

class App extends Basic
{
    /**
     * @var object
     */
    protected $_attr = [
        'router/event/up'               => null,
        'router/event/down'             => null,
        'router/controller/default'     => 'main',
        'router/action/default'         => 'default',
        'router/action/error'           => 'error',

        'controller/namespace'          => null,        // Requied
        'controller/path'               => null,        // Requied

        // 'model/namespace'               => null,        // Requied
        // 'model/path'                    => null,        // Requied

        'view/paths'                    => null,        // Requied
        'view/ext'                      => 'php',

        'static/paths'                  => null,        // Requied

        'cache/path'                    => null,        // Requied
        'cache/permission'              => 0775,        // rwxrwxr-x
        'cache/time'                    => 300          // 300 sec = 5 min
    ];

    /**
     * @var object
     */
    protected $_mimeMapping = [
        'html'  => 'text/html',
        'css'   => 'text/css',
        'js'    => 'text/javascript',
        'json'  => 'application/json',
        'xml'   => 'application/xml',

        'jpg'   => 'image/jpeg',
        'png'   => 'image/png',
        'gif'   => 'image/gif',

        'woff'  => 'application/font-woff',
        'ttf'   => 'font/opentype'
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
     * Construct
     */
    public function __construct()
    {
        $this->req = Req::init();
        $this->res = Res::init();
    }

    /**
     * Up Function
     */
    private function up()
    {
        // // Register Model Classes
        // $namespace = $this->getAttr('model/namespace');
        // $path = $this->getAttr('model/path');

        // if (true === is_string($namespace) && true === is_string($path)) {
        //     Loader::append($namespace, $path);
        // }

        // Register Controller Classes & Load
        $namespace = $this->getAttr('controller/namespace');
        $path = $this->getAttr('controller/path');

        if (true === is_string($namespace) && true === is_string($path)) {
            Loader::append($namespace, $path);
        }

        $upEvent = $this->getAttr('router/event/up');

        if (true === is_callable($upEvent)) {
            return $upEvent();
        }

        return true;
    }

    /**
     * Down Function
     */
    private function down()
    {
        $downEvent = $this->getAttr('router/event/down');

        if (true === is_callable($downEvent)) {
            $downEvent();
        }
    }

    /**
     * Run Application
     *
     * @return bool
     */
    public function run()
    {
        if (false !== $this->up()) {
            if ('get' === $this->req->method()) {

                // Load Static File
                if (true === $this->loadStatic()) {
                    $this->down();

                    return true;
                }

                // Load Cache File
                if (true === $this->loadCache()) {
                    $this->down();

                    return true;
                }
            }

            // Load Controller to Handle
            if (true === $this->loadController()) {
                $this->down();

                return true;
            }
        }

        // Not executed anything
        if (200 === http_response_code()) {
            http_response_code(400);
        }

        return false;
    }

    /**
     * Load Static File
     *
     * @return bool
     */
    private function loadStatic(): bool
    {
        $paths = $this->getAttr('static/paths');
        $uri = $this->req->uri();

        if (false === is_array($paths) || '' === $uri) {
            return false;
        }

        $currentPath = null;

        foreach ($paths as $path) {
            if (false === file_exists("{$path}/{$uri}")) {
                continue;
            }

            $currentPath = "{$path}/{$uri}";

            break;
        }

        if (null === $currentPath) {
            return false;
        }

        $mimeType = null;
        $fileInfo = pathinfo($currentPath);

        // Check File Ext
        if (true === isset($fileInfo['extension'])) {

            // Skip .php File
            if ('php' === $fileInfo['extension']) {
                return false;
            }

            // Check MIME Type
            if (true === isset($this->_mimeMapping[$fileInfo['extension']])) {
                $mimeType = $this->_mimeMapping[$fileInfo['extension']];
            }
        }

        // Using Builtin Function to Check MIME Type
        if (null === $mimeType) {
            $mimeType = mime_content_type($currentPath);
        }

        // Set HTTP Header
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($currentPath));

        echo file_get_contents($currentPath);

        return true;
    }

    /**
     * Load Cache File
     *
     * @return bool
     */
    private function loadCache(): bool
    {
        $path = $this->getAttr('cache/path');

        if (false === is_string($path)) {
            return false;
        }

        $currentPath = "{$path}/" . md5($this->req->uri());

        if (false === file_exists($currentPath)) {
            return false;
        }

        // Check File Create Time
        if (time() - filectime($currentPath) > $this->getAttr('cache/time')) {
            unlink($currentPath);

            return false;
        }

        $this->res->html(file_get_contents($currentPath));

        return true;
    }

    /**
     * Save Cache File
     *
     * @return bool
     */
    private function saveCache($html): bool
    {
        $path = $this->getAttr('cache/path');

        if (false === is_string($path)) {
            return false;
        }

        if (false === file_exists($path)) {
            $permission = $this->getAttr('cache/permission');

            mkdir($path, $permission, true);
        }

        $currentPath = "{$path}/" . md5($this->req->uri());

        file_put_contents($currentPath, $html);

        return true;
    }

    /**
     * Load Controller
     *
     * @return bool
     */
    private function loadController(): bool
    {
        $namespace = $this->getAttr('controller/namespace');
        $path = $this->getAttr('controller/path');

        if (false === is_string($namespace) || false === is_string($path)) {
            return false;
        }

        $params = explode('/', $this->req->uri());
        $params = '' !== $params[0] ? $params : [];
        $currentPath = null;

        while (0 < count($params)) {
            $tempPath = ucfirst($params[0]);
            $tempPath = (null !== $currentPath) ? "{$currentPath}/{$tempPath}" : $tempPath;

            if (false === file_exists("{$path}/{$tempPath}")
                && false === file_exists("{$path}/{$tempPath}Controller.php")
            ) {
                break;
            }

            $currentPath = $tempPath;

            array_shift($params);
        }

        // Rewrite Controller
        if (null === $currentPath) {
            $controllerName = ucfirst($this->getAttr('router/controller/default'));

            if (false === file_exists("{$path}/{$controllerName}Controller.php")) {
                http_response_code(400);

                return false;
            }

            $currentPath = $controllerName;
        }

        $className = implode('\\', explode('/', $currentPath));
        $className = "{$namespace}\\{$className}Controller";

        $instance = new $className();

        switch ($instance->getAttr('mode')) {
        case 'page':
            $actionName = null;

            // Custom Handler
            if ( 0 < count($params)) {
                $actionName = array_shift($params);
            }

            // Default Handler
            if (null === $actionName) {
                $actionName = $this->getAttr('router/action/default');
            }

            if (false === method_exists($instance, "{$actionName}Action")) {
                $controllerName = ucfirst($this->getAttr('router/controller/default'));
                $actionName = $this->getAttr('router/action/error');

                if (false === file_exists("{$path}/{$controllerName}Controller.php")) {
                    http_response_code(404);

                    return false;
                }

                $currentPath = $controllerName;

                $className = implode('\\', explode('/', $currentPath));
                $className = "{$namespace}\\{$className}Controller";

                $instance = new $className();

                if (false === method_exists($instance, "{$actionName}Action")) {
                    http_response_code(404);

                    return false;
                }
            }

            // Init View
            $view = View::init();
            $view->setAttr('paths', $this->getAttr('view/paths'));
            $view->setAttr('ext', $this->getAttr('view/ext'));
            $view->setLayoutPath(implode('/', array_map(function ($segment) {
                return strtolower($segment);
            }, explode('/', "{$currentPath}/{$actionName}"))));

            // Controller Flow
            if (false !== $instance->up()) {
                $method = "{$actionName}Action";

                if (false !== $instance->$method($params)) {

                    // Render HTML
                    $data = $view->render();

                    // Save Cache
                    if ('get' === $this->req->method()) {
                        $this->saveCache($data);
                    }

                    $this->res->html($data);
                }
            }

            $instance->down();

            break;
        case 'ajax':
            $actionName = null;

            // Custom Handler
            if (null === $actionName && 0 < count($params)) {
                $actionName = array_shift($params);
            }

            // Default Handler
            if (null === $actionName) {
                $actionName = $this->getAttr('router/action/default');
            }

            if (false === method_exists($instance, "{$actionName}Action")) {
                http_response_code(501);

                return false;
            }

            // Controller Flow
            if (false !== $instance->up()) {
                $method = "{$actionName}Action";
                $data = $instance->$method($params);

                $this->res->json($data);
            }

            $instance->down();

            break;
        case 'rest':
            $actionName = $this->req->method();

            if (false === method_exists($instance, "{$actionName}Action")) {
                http_response_code(501);

                return false;
            }

            // Controller Flow
            if (false !== $instance->up()) {
                $method = "{$actionName}Action";
                $result = $instance->$method($params);

                $this->res->json($result);
            }

            $instance->down();

            break;
        default:
            return false;
        }

        return true;
    }
}
