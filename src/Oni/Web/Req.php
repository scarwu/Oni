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

class Req extends
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
     * Content Length
     *
     * @return integer
     */
    public function contentLength()
    {
        return isset($_SERVER['CONTENT_LENGTH']) && '' !== $_SERVER['CONTENT_LENGTH']
            ? (int) $_SERVER['CONTENT_LENGTH'] : 0;
    }

    /**
     * Content Type
     *
     * @return string|null
     */
    public function contentType()
    {
        // Content Type
        //     * text/plain
        //     * multipart/form-data
        //     * multipart/form-data; boundary=----WebKitFormBoundaryKw2qnJFfEWBNPPYK
        //     * application/x-www-form-urlencoded
        //     * application/json
        return isset($_SERVER['CONTENT_TYPE']) && '' !== $_SERVER['CONTENT_TYPE']
            ? explode(';', $_SERVER['CONTENT_TYPE'])[0] : null;
    }

    /**
     * Get Protocol
     *
     * @return string
     */
    public function protocol()
    {
        return strtolower($_SERVER['SERVER_PROTOCOL']);
    }

    /**
     * Get Scheme
     *
     * @return string
     */
    public function scheme()
    {
        return strtolower($_SERVER['REQUEST_SCHEME']);
    }

    /**
     * Get Host
     *
     * @return string
     */
    public function host()
    {
        return $_SERVER['HTTP_HOST'];
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
     * Body
     */
    public function body()
    {
        return file_get_contents('php://input');
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
        case 'multipart/form-data':
            return $_POST;
        case 'application/json':
            return json_decode($this->body(), true);
        default:
            return $this->body();
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
            && 'XMLHttpRequest' === $_SERVER['HTTP_X_REQUESTED_WITH'];
    }
}
