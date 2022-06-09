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
        'controller/namespace'          => null,        // Requied
        'controller/path'               => null,        // Requied
        'controller/default/handler'    => 'Main',
        'controller/default/action'     => 'default',
        'controller/error/handler'      => 'Main',
        'controller/error/action'       => 'error',

        'model/namespace'               => null,        // Requied
        'model/path'                    => null,        // Requied

        'view/path'                     => null,        // Requied
        'view/ext'                      => 'php',

        'static/path'                   => null,        // Requied

        'cache/path'                    => null,        // Requied
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
     * Run Application
     *
     * @return bool
     */
    public function run(): bool
    {
        if ('get' === $this->req->method()) {

            // Load Static File
            if (true === $this->loadStatic()) {
                return true;
            }

            // Load Cache File
            if (true === $this->loadCache()) {
                return true;
            }
        }

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

            if (true === $this->loadController()) {
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
    private function loadStatic(): bool
    {
        $path = $this->getAttr('static/path');
        $uri = $this->req->uri();

        if (false === is_string($path) || '' === $uri) {
            return false;
        }

        $currentPath = "{$path}/{$uri}";

        if (false === file_exists($currentPath)) {
            return false;
        }

        // Load Real File from Disk
        return $this->loadRealFile($currentPath);
    }

    /**
     * Load Cache File
     *
     * @return bool
     */
    private function loadCache(): bool
    {
        $path = $this->getAttr('cache/path');
        $uri = $this->req->uri();

        if (false === is_string($path) || '' === $uri) {
            return false;
        }

        $currentPath = "{$path}/" . md5($uri);

        if (false === file_exists($currentPath)) {
            return false;
        }

        // Check File Create Time
        if (time() - filectime($currentPath) > $this->getAttr('cache/time')) {
            unlink($currentPath);

            return false;
        }

        // Load Real File from Disk
        return $this->loadRealFile($currentPath);
    }

    /**
     * Load Real File
     *
     * @param string $currentPath
     *
     * @return bool
     */
    private function loadRealFile(string $currentPath): bool
    {
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
     * Load Controller
     *
     * @return bool
     */
    private function loadController(): bool
    {
        $namespace = $this->getAttr('controller/namespace');
        $path = $this->getAttr('controller/path');

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
        $action = null;

        if (null === $currentPath) {
            $handler = ucfirst($this->getAttr('controller/default/handler'));

            if (false === file_exists("{$path}/{$handler}Controller.php")) {
                $handler = ucfirst($this->getAttr('controller/error/handler'));

                if (false === file_exists("{$path}/{$handler}Controller.php")) {
                    http_response_code(400);

                    return false;
                }

                $action = $this->getAttr('controller/error/action');
            }

            $currentPath = $handler;
        }

        // Controller Flow
        $currentNamaspece = implode('\\', explode('/', $currentPath));
        $currentNamaspece = "{$namespace}\\{$currentNamaspece}Controller";

        $instance = new $currentNamaspece();

        switch ($instance->getAttr('mode')) {
        case 'page':
            if (null === $action && 0 === count($params)) {
                $action = $this->getAttr('controller/default/action');
            } else {
                $action = array_shift($params);
            }

            $currentAction = "{$action}Action";

            if (false === method_exists($instance, $currentAction)) {
                $handler = ucfirst($this->getAttr('controller/error/handler'));

                if (false === file_exists("{$path}/{$handler}Controller.php")) {
                    http_response_code(404);

                    return false;
                }

                $action = $this->getAttr('controller/error/action');
                $currentPath = $handler;

                $currentNamaspece = implode('\\', explode('/', $currentPath));
                $currentNamaspece = "{$namespace}\\{$currentNamaspece}Controller";

                $instance = new $currentNamaspece();
                $currentAction = "{$action}Action";

                if (false === method_exists($instance, $currentAction)) {
                    http_response_code(404);

                    return false;
                }
            }

            if (false !== $instance->up()) {

                // Init View
                $view = View::init();
                $view->setAttr('path', $this->getAttr('view/path'));
                $view->setAttr('ext', $this->getAttr('view/ext'));
                $view->setLayoutPath(implode('/', array_map(function ($segment) {
                    return strtolower($segment);
                }, explode('/', "{$currentPath}/{$action}"))));

                $instance->$currentAction($params);

                $this->res->html($view->render());
            }

            $instance->down();

            break;
        case 'ajax':
            if (null === $action && 0 === count($params)) {
                $action = $this->getAttr('controller/default/action');
            } else {
                $action = array_shift($params);
            }

            $currentAction = "{$action}Action";

            if (false === method_exists($instance, $currentAction)) {
                http_response_code(501);

                return false;
            }

            if (false !== $instance->up()) {
                $result = $instance->$currentAction($params);

                $this->res->json($result);
            }

            $instance->down();

            break;
        case 'rest':
            $currentAction = $this->req->method() . 'Action';

            if (false === method_exists($instance, $currentAction)) {
                http_response_code(501);

                return false;
            }

            if (false !== $instance->up()) {
                $result = $instance->$currentAction($params);

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
