<?php
/**
 * Request
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace Oni\Web;

use Exception;
use Oni\Basic;

class Req extends Basic
{
    /**
     * @var object
     */
    private static $_instance = null;

    /**
     * Construct
     *
     * This function is private, so this class is singleton pattern
     */
    private function __construct() {}

    /**
     * Initialize
     */
    public static function init()
    {
        if (null === self::$_instance) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    /**
     * Method
     *
     * @return string
     */
    public function method()
    {
        $method = isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])
            ? $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']
            : $_SERVER['REQUEST_METHOD'];

        return strtolower($method);
    }

    /**
     * Content Type
     *
     * @return string|null
     */
    public function contentType()
    {
        // Content Type
        //     * multipart/form-data
        //     * application/x-www-form-urlencoded
        //     * application/json
        return isset($_SERVER['CONTENT_TYPE'])
            ? $_SERVER['CONTENT_TYPE'] : null;
    }

    /**
     * Get URI
     *
     * @return string
     */
    public function uri()
    {
        $uri = null;

        if (isset($_SERVER['PATH_INFO'])) {
            $uri = $_SERVER['PATH_INFO'];
        } elseif (isset($_SERVER['REQUEST_URI'])) {
            $uri = explode('?', $_SERVER['REQUEST_URI'])[0];
        }

        return trim($uri, '/');
    }

    /**
     * Query
     */
    public function query()
    {
        return $_GET;
    }

    /**
     * Content
     */
    public function content()
    {
        switch ($this->contentType()) {
        case 'application/x-www-form-urlencoded':
            return urldecode(file_get_contents('php://input'));
        case 'application/json':
            return json_decode(file_get_contents('php://input'), true);
        case 'multipart/form-data':
        default:
            return $_POST;
        }
    }

    /**
     * File
     */
    public function file()
    {
        switch ($this->contentType()) {
        case 'multipart/form-data':
        default:
            return $_FILES;
        }
    }

    /**
     * Is Ajax
     */
    public function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            ? $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest'
            : false;
    }
}
