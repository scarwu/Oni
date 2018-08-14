<?php
/**
 * Response
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace Oni\Web;

class Res extends
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
     * Redirect
     *
     * @param string $path
     */
    public function redirect($path)
    {
        header("Location: {$path}");
    }

    /**
     * Render HTML
     *
     * @param string $data
     */
    public function html($data)
    {
        header('Content-Type: text/html');

        echo $data;
    }

    /**
     * Render JSON
     *
     * @param array $data
     * @param integer $option
     */
    public function json($data = null, $option = null)
    {
        header('Content-Type: application/json');

        if (null !== $data) {
            echo json_encode($data, $option);
        }
    }
}
