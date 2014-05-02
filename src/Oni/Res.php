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
    public static function html($_template, $_data= [])
    {
        $_path = self::$res['path'] . "/$_template.phtml";

        if (file_exists($_path)) {
            foreach ($_data as $_key => $_value) {
                $$_key = $_value;
            }

            header('Content-Type: text/html');
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
        if (null !== $json) {
            header('Content-Type: application/json');
            echo json_encode($json, $option);
        }
    }
}
