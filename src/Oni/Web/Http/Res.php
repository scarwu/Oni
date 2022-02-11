<?php
/**
 * Response
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace Oni\Web\Http;

class Res
{
    /**
     * @var object
     */
    private static $_instance = null;

    /**
     * Initialize
     */
    public static function init(): object
    {
        if (null === self::$_instance) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    /**
     * Construct
     *
     * This function is private, so this class is singleton pattern
     */
    private function __construct() {}

    /**
     * Redirect
     *
     * @param string $path
     */
    public function redirect(string $path): void
    {
        header("Location: {$path}");
    }

    /**
     * Render HTML
     *
     * @param string $data
     */
    public function html(string $data): void
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
    public function json(array $data, ?integer $option = null): void
    {
        header('Content-Type: application/json');

        echo true === isset($option)
            ? json_encode($data, $option)
            : json_encode($data);
    }
}
