<?php
/**
 * IO
 *
 * @package     Oni
 * @author      ScarWu
 * @copyright   Copyright (c) ScarWu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace Oni\CLI;

class IO
{
    private function __construct() {}

    /**
     * @var array
     */
    private static $text_color = [
        'black' => '0;30',
        'red' => '0;31',
        'green' => '0;32',
        'brown' => '0;33',
        'blue' => '0;34',
        'purple' => '0;35',
        'cyan' => '0;36',
        'light_gray' => '0;37',

        'dark_gray' => '1;30',
        'light_red' => '1;31',
        'light_green' => '1;32',
        'yellow' => '1;33',
        'light_blue' => '1;34',
        'light_purple' => '1;35',
        'light_cyan' => '1;36',
        'white' => '1;37'
    ];

    /**
     * @var array
     */
    private static $bg_color = [
        'black' => '0;40',
        'red' => '0;41',
        'green' => '0;42',
        'brown' => '0;43',
        'blue' => '0;44',
        'purple' => '0;45',
        'cyan' => '0;46',
        'light_gray' => '0;47',

        'dark_gray' => '1;40',
        'light_red' => '1;41',
        'light_green' => '1;42',
        'yellow' => '1;43',
        'light_blue' => '1;44',
        'light_purple' => '1;45',
        'light_cyan' => '1;46',
        'white' => '1;47'
    ];

    private static function color($text, $text_color = null, $bg_color = null)
    {
        if (isset(self::$text_color[$text_color])) {
            $color = self::$text_color[$text_color];
            $text = "\033[{$color}m{$text}\033[m";
        }

        if (isset(self::$bg_color[$bg_color])) {
            $color = self::$bg_color[$bg_color];
            $text = "\033[{$color}m{$text}\033[m";
        }

        return $text;
    }

    /**
     * Write data to STDOUT
     *
     * @param string text
     * @param string text_color
     * @param string bg_color
     */
    public static function write($text, $text_color = null, $bg_color = null)
    {
        if (null !== $text_color || null !== $bg_color) {
            $text = self::color($text, $text_color, $bg_color);
        }

        fwrite(STDOUT, $text);
    }

    /**
     * Write data to STDOUT
     *
     * @param string text
     * @param string bg_color
     * @param string bg_color
     */
    public static function writeln($text = '', $text_color = null, $bg_color = null)
    {
        self::write("{$text}\n", $text_color, $bg_color);
    }

    /**
     * Error
     *
     * @param string text
     */
    public static function error($text)
    {
        self::write("{$text}\n", 'red');
    }

    /**
     * Warning
     *
     * @param string text
     */
    public static function warning($text)
    {
        self::write("{$text}\n", 'yellow');
    }

    /**
     * Notice
     *
     * @param string text
     */
    public static function notice($text)
    {
        self::write("{$text}\n", 'green');
    }

    /**
     * Info
     *
     * @param string text
     */
    public static function info($text)
    {
        self::write("{$text}\n", 'dark_gray');
    }

    /**
     * Debug
     *
     * @param string text
     */
    public static function debug($text)
    {
        self::write("{$text}\n", 'light_gray');
    }

    /**
     * Log
     *
     * @param string text
     */
    public static function log($text)
    {
        self::write("{$text}\n");
    }

    /**
     * Read STDIN
     *
     * @return string
     */
    public static function read()
    {
        return trim(fgets(STDIN));
    }

    /**
     * Ask
     *
     * @param string text
     * @param function callback
     * @param string text_color
     * @param string bg_color
     *
     * @return string
     */
    public static function ask($text, $callback = null, $text_color = null, $bg_color = null)
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
