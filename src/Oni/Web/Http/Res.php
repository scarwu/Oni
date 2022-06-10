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
        header('Content-Length: ' . strlen($data));

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
        $data = (true === isset($option))
            ? json_encode($data, $option)
            : json_encode($data);

        header('Content-Type: application/json');
        header('Content-Length: ' . strlen($data));

        echo $data;
    }
}
