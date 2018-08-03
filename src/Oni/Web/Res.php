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
    /**
     * @var Array
     */
    private static $res;

    private function __construct()
    {
        // nothing here
    }

    /**
     * Initialize Response Module
     *
     * @param Array
     */
    public static function init($res)
    {
        self::$res = $res;
    }

    /**
     * Render HTML
     *
     * @param String
     * @param Array
     */
    public static function html($_view, $_data= [])
    {
        header('Content-Type: text/html');

        $_path = self::$res['path'] . "/{$_view}.php";

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
     * @param Array
     */
    public static function json($json = null, $option = null)
    {
        header('Content-Type: application/json');

        if (null !== $json) {
            echo json_encode($json, $option);
        }
    }
}
