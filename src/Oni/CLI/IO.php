<?php
/**
 * Input & Output
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace Oni\CLI;

use Oni\Basic;

class IO extends Basic
{
    /**
     * @var object
     */
    private static $_instance = null;

    /**
     * @var array
     */
    private $_arguments = [];

    /**
     * @var array
     */
    private $_options = [];

    /**
     * @var array
     */
    private $_configs = [];

    /**
     * Construct
     *
     * This function is private, so this class is singleton pattern
     */
    private function __construct()
    {
        $configRegexRule = '/^-{2}(\w+(?:-\w+)?)(?:=(.+))?/';
        $optionRegexRule = '/^-{1}(\w+)/';

        $argv = array_slice($_SERVER['argv'], 1);

        // arguments
        while ($argv) {
            if (true === (bool) preg_match($configRegexRule, $argv[0])) {
                break;
            }

            if (true === (bool) preg_match($optionRegexRule, $argv[0])) {
                break;
            }

            $this->_arguments[] = array_shift($argv);
        }

        // options & configs
        while ($value = array_shift($argv)) {
            if (true === (bool) preg_match($configRegexRule, $value, $match)) {
                $this->_configs[$match[1]] = isset($match[2]) ? $match[2] : null;
            }

            if (true === (bool) preg_match($optionRegexRule, $value, $match)) {
                $this->_options[$match[1]] = null;

                if (isset($argv[0])) {
                    if (true === (bool) preg_match($configRegexRule, $argv[0])) {
                        continue;
                    }

                    if (true === (bool) preg_match($optionRegexRule, $argv[0])) {
                        continue;
                    }

                    $this->_options[$match[1]] = array_shift($argv);
                }
            }
        }
    }

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
     * Get Arguments
     *
     * @return integer $index
     *
     * @return array|bool
     */
    public function getArguments($index = null)
    {
        if (true === is_integer($index)) {
            if (true === array_key_exists($index, $this->_arguments)) {
                return $this->_arguments[$index];
            } else {
                return false;
            }
        }

        return $this->_arguments;
    }

    /**
     * Has Arguments
     *
     * @return bool
     */
    public function hasArguments()
    {
        return sizeof($this->_arguments) > 0;
    }

    /**
     * Get Options
     *
     * @return string $key
     *
     * @return array|bool
     */
    public function getOptions($key = null)
    {
        if (true === is_string($key)) {
            if (true === array_key_exists($key, $this->_options)) {
                return $this->_options[$key];
            } else {
                return false;
            }
        }

        return $this->_options;
    }

    /**
     * Has Options
     *
     * @return string $key
     *
     * @return bool
     */
    public function hasOptions($key = null)
    {
        if (true === is_string($key)) {
            return array_key_exists($key, $this->_options);
        }

        return sizeof($this->_options) > 0;
    }

    /**
     * Get Configs
     *
     * @return string $key
     *
     * @return array|bool
     */
    public function getConfigs($key = null)
    {
        if (true === is_string($key)) {
            if (true === array_key_exists($key, $this->_configs)) {
                return $this->_configs[$key];
            } else {
                return false;
            }
        }

        return $this->_configs;
    }

    /**
     * Has Configs
     *
     * @return string $key
     *
     * @return bool
     */
    public function hasConfigs($key = null)
    {
        if (true === is_string($key)) {
            return array_key_exists($key, $this->_configs);
        }

        return sizeof($this->_configs) > 0;
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
     * @param string $textColor
     * @param string $bgColor
     *
     * @return string|bool
     */
    public function ask($text, $callback = null, $textColor = null, $bgColor = null)
    {
        if (null === $callback) {
            $callback = function() {
                return true;
            };
        }

        do {
            $this->write($text, $textColor, $bgColor);
        } while (false === $callback($answer = $this->read()));

        return $answer;
    }

    /**
     * @var array
     */
    private static $textColor = [
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
    private static $bgColor = [
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

    /**
     * Color
     *
     * @param string $text
     * @param string $textColor
     * @param string $bgColor
     *
     * @return string
     */
    private function color($text, $textColor = null, $bgColor = null)
    {
        if (true === isset(self::$textColor[$textColor])) {
            $color = self::$textColor[$textColor];
            $text = "\033[{$color}m{$text}\033[m";
        }

        if (true === isset(self::$bgColor[$bgColor])) {
            $color = self::$bgColor[$bgColor];
            $text = "\033[{$color}m{$text}\033[m";
        }

        return $text;
    }

    /**
     * Write data to STDOUT
     *
     * @param string $text
     * @param string $textColor
     * @param string $bgColor
     */
    public function write($text, $textColor = null, $bgColor = null)
    {
        if (null !== $textColor || null !== $bgColor) {
            $text = $this->color($text, $textColor, $bgColor);
        }

        fwrite(STDOUT, $text);
    }

    /**
     * Write data to STDOUT
     *
     * @param string $text
     * @param string $bgColor
     * @param string $bgColor
     */
    public function writeln($text = '', $textColor = null, $bgColor = null)
    {
        $this->write("{$text}\n", $textColor, $bgColor);
    }

    /**
     * Error
     *
     * @param string $text
     */
    public function error($text)
    {
        $this->write("{$text}\n", 'red');
    }

    /**
     * Warning
     *
     * @param string $text
     */
    public function warning($text)
    {
        $this->write("{$text}\n", 'yellow');
    }

    /**
     * Notice
     *
     * @param string $text
     */
    public function notice($text)
    {
        $this->write("{$text}\n", 'green');
    }

    /**
     * Info
     *
     * @param string $text
     */
    public function info($text)
    {
        $this->write("{$text}\n", 'dark_gray');
    }

    /**
     * Debug
     *
     * @param string $text
     */
    public function debug($text)
    {
        $this->write("{$text}\n", 'light_gray');
    }

    /**
     * Log
     *
     * @param string $text
     */
    public function log($text)
    {
        $this->write("{$text}\n");
    }
}
