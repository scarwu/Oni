<?php
/**
 * Oni Request Module
 *
 * @package     Oni
 * @author      ScarWu
 * @copyright   Copyright (c) ScarWu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace Oni\Web;

class Req
{
    /**
     * @var Array
     */
    private static $instance = null;

    private function __construct()
    {
        // nothing here
    }

    public function header()
    {
        return $_SERVER;
    }

    /**
     * Content Type
     * multipart/form-data
     * application/x-www-form-urlencoded
     * application/json
     *
     */

    /**
     * Is Ajax Request
     */
    public function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            ? $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest'
            : false;
    }

    /**
     * Get HTTP Request Method
     */
    public function method()
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    public function path()
    {
        return isset($_SERVER['PATH_INFO'])
            ? $_SERVER['PATH_INFO'] : null;
    }

    public function contentType()
    {
        return isset($_SERVER['CONTENT_TYPE'])
            ? $_SERVER['CONTENT_TYPE'] : null;
    }

    /**
     * Get URL Parameter
     */
    public function urlParams()
    {
        return $_GET;
    }

    /**
     * Get Content
     */
    public function content()
    {
        if ('application/json' === $_SERVER['CONTENT_TYPE']) {
            return json_decode(file_get_contents('php://input'), true);
        }

        return $_POST;
    }

    /**
     * Get File
     */
    public function file()
    {
        return $_FILES;
    }

    /**
     * Initialize Request Module
     *
     * @param Array
     */
    public static function init()
    {
        if (null === self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }
}
