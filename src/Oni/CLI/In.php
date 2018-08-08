<?php
/**
 * Input
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace Oni\CLI;

use Exception;
use Oni\Basic;

class In extends Basic
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
     * Read STDIN
     *
     * @return string
     */
    public function read()
    {
        return trim(fgets(STDIN));
    }

    /**
     * Ask
     *
     * @param string $text
     * @param function $callback
     * @param string $text_color
     * @param string $bg_color
     *
     * @return string|bool
     */
    public function ask($text, $callback = null, $text_color = null, $bg_color = null)
    {
        if (null === $callback) {
            $callback = function() {
                return true;
            };
        }

        do {
            self::write($text, $text_color, $bg_color);
        } while (!$callback($answer = self::read()));

        return $answer;
    }
}
