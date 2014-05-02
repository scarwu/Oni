<?php
/**
 * Oni Response Module
 * 
 * @package     Oni
 * @author      ScarWu
 * @copyright   Copyright (c) 2014, ScarWu (http://scar.simcz.tw/)
 * @link        http://github.com/scarwu/Oni
 */

namespace Oni;

class Res
{
    static private $res;
    static private $http_status_code = [

        // 1xx Informational
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing', // (WebDAV) (RFC 2518)
        103 => 'Checkpoint',
        122 => 'Request-URI too long',
        
        // 2xx Success
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information', // (since HTTP/1.1)
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status', // (WebDAV) (RFC 4918)
        208 => 'Already Reported', // (WebDAV) (RFC 5842)
        226 => 'IM Used', // (RFC 3229)
        
        // 3xx Redirection
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other', // (since HTTP/1.1)
        304 => 'Not Modified',
        305 => 'Use Proxy', // (since HTTP/1.1)
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect', // (since HTTP/1.1)
        308 => 'Resume Incomplete',
        
        // 4xx Client Error
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot', // (RFC 2324)
        422 => 'Unprocessable Entity',
        423 => 'Locked', // (WebDAV) (RFC 4918)
        424 => 'Failed Dependency', // (WebDAV) (RFC 4918)
        425 => 'Unordered Collection', // (RFC 3648)
        426 => 'Upgrade Required', // (RFC 2817)
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        444 => 'No Response',
        449 => 'Retry With',
        450 => 'Blocked by Windows Parental Controls',
        499 => 'Client Closed Request',
        
        // 5xx Server Error
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates', // (RFC 2295)
        507 => 'Insufficient Storage', // (WebDAV) (RFC 4918)
        508 => 'Loop Detected', // (WebDAV) (RFC 5842)
        509 => 'Bandwidth Limit Exceeded', // (Apache bw/limited extension)
        510 => 'Not Extended', // (RFC 2774)
        511 => 'Network Authentication Required',
        598 => 'Network read timeout error',
        599 => 'Network connect timeout error'
    ];

    private function __construct()
    {
        // nothing here
    }

    static function init($res)
    {
        self::$res = $res;
    }

    /**
     * Send HTTP Status Code
     * 
     * @param Integer
     */
    static public function code($code)
    {
        if (in_array($code, self::$http_status_code)) {
            $msg = self::$http_status_code[$code];
            header("HTTP/1.1 $code $msg");
        }
    }

    /**
     * Render HTML
     * 
     * @param String
     * @param Array
     */
    static public function html($_template, $_data= [])
    {
        $_template_path = self::$res['path'] . "/$_template.phtml";

        if (file_exists($_template_path)) {
            header('Content-Type: text/html; charset=utf-8');
            foreach ($_data as $_key => $_value) {
                $$_key = $_value;
            }

            include $_template_path;
        }
    }

    /**
     * Render JSON
     * 
     * @param Array
     */
    static public function json($json = null)
    {
        if (null === $json) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($json);
        }
    }
}
