<?php
/**
 * Oni Response Module
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace Oni\Web;

class Res
{
    private function __construct() {}

    /**
     * @var object
     */
    private static $_instance = null;

    /**
     * @var array
     */
    private static $_attr = [
        'view' => false,
        'view/ext' => 'php'
    ];

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
     * Set Attr
     *
     * @param string $key
     * @param string $value
     *
     * @return object
     */
    public function setAttr($key, $value)
    {
        self::$_attr[$key] = $value;

        return $this;
    }

    /**
     * Get Attr
     *
     * @param string $key
     *
     * @return object|null
     */
    public function getAttr($key)
    {
        return isset(self::$_attr[$key])
            ? self::$_attr[$key] : null;
    }

    /**
     * Render HTML
     *
     * @param string $_name
     * @param array $_data
     */
    public static function html($_name, $_data = [])
    {
        header('Content-Type: text/html');

        $_prefix = self::$_attr['view'];
        $_ext = self::$_attr['view/ext'];
        $_path = "{$_prefix}/{$_name}.{$_ext}";

        if (file_exists($_path)) {
            foreach ($_data as $_key => $_value) {
                $$_key = $_value;
            }

            include $_path;
        }
    }

    /**
     * Render JSON
     *
     * @param array $data
     * @param integer $option
     */
    public static function json($data = null, $option = null)
    {
        header('Content-Type: application/json');

        if (null !== $data) {
            echo json_encode($data, $option);
        }
    }
}
