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
use Oni\CLI\IO\ANSIEscapeCode as AEC;

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
            if (preg_match($configRegexRule, $argv[0])) {
                break;
            }

            if (preg_match($optionRegexRule, $argv[0])) {
                break;
            }

            $this->_arguments[] = array_shift($argv);
        }

        // options & configs
        while ($value = array_shift($argv)) {
            if (preg_match($configRegexRule, $value, $match)) {
                $this->_configs[$match[1]] = isset($match[2]) ? $match[2] : null;
            }

            if (preg_match($optionRegexRule, $value, $match)) {
                $this->_options[$match[1]] = null;

                if (isset($argv[0])) {
                    if (preg_match($configRegexRule, $argv[0])) {
                        continue;
                    }

                    if (preg_match($optionRegexRule, $argv[0])) {
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
        if (is_integer($index)) {
            if (array_key_exists($index, $this->_arguments)) {
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
        if (is_string($key)) {
            if (array_key_exists($key, $this->_options)) {
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
        if (is_string($key)) {
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
        if (is_string($key)) {
            if (array_key_exists($key, $this->_configs)) {
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
        if (is_string($key)) {
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
     * @param string $fgColor
     * @param string $bgColor
     *
     * @return string|bool
     */
    public function ask($text, $callback = null, $fgColor = null, $bgColor = null)
    {
        if (null === $callback) {
            $callback = function() {
                return true;
            };
        }

        do {
            $this->write($text, $fgColor, $bgColor);
        } while (false === $callback($answer = $this->read()));

        return $answer;
    }

    /**
     * Menu Render
     *
     * @param array $options
     * @param integer $selectedIndex
     */
    private function menuRender($options, $selectedIndex) {
        $this->write(implode("\n", array_map(function ($option, $currentIndex) use ($selectedIndex) {
            return ($selectedIndex === $currentIndex)
                ? "> {$option}" : "  {$option}";
        }, $options, array_keys($options))));
    }

    /**
     * Menu Input
     *
     * @param array $options
     */
    // public function menuInput($options) {
    //     $totalIndex = count($options);
    //     $selectedIndex = 0;
    //     $isBreakLoop = false;
    //     $char = null;

    //     readline_callback_handler_install('', function() {});

    //     do {
    //         switch (ord($char)) {
    //         case 10: // Enter Key
    //             $isBreakLoop = true;

    //             break;
    //         case 65: // Up Key
    //             if ($selectedIndex - 1 >= 0) {
    //                 $selectedIndex--;
    //             }

    //             break;
    //         case 66: // Down Key
    //             if ($selectedIndex + 1 < $totalIndex) {
    //                 $selectedIndex++;
    //             }

    //             break;
    //         }

    //         if ($isBreakLoop) {
    //             break;
    //         }

    //         $this->menuRender($options, $selectedIndex);
    //     } while ($char = stream_get_contents(STDIN, 1));

    //     readline_callback_handler_remove();

    //     return $selectedIndex;
    // }

    /**
     * Menu Select
     *
     * @param array $options
     */
    public function menuSelect($options) {
        $totalIndex = count($options);
        $selectedIndex = 0;
        $isBreakLoop = false;
        $char = null;

        $wWidth = (int) exec('tput cols');
        $wHeight = (int) exec('tput lines');
        $bHeight = count($options);

        readline_callback_handler_install('', function() {});

        do {
            switch (ord($char)) {
            case 10: // Enter Key
                $isBreakLoop = true;

                break;
            case 65: // Up Key
                if ($selectedIndex - 1 >= 0) {
                    $selectedIndex--;
                }

                break;
            case 66: // Down Key
                if ($selectedIndex + 1 < $totalIndex) {
                    $selectedIndex++;
                }

                break;
            }

            if ($isBreakLoop) {
                break;
            }

            $this->menuRender($options, $selectedIndex);

            $this->write(AEC::moveTo(0, $wHeight - $bHeight));
        } while ($char = stream_get_contents(STDIN, 1));

        $this->writeln(AEC::moveTo(0, $wHeight));

        readline_callback_handler_remove();

        return $selectedIndex;
    }

    /**
     * Write data to STDOUT
     *
     * @param string $text
     * @param string $fgColor
     * @param string $bgColor
     */
    public function write($text, $fgColor = null, $bgColor = null)
    {
        if (null !== $fgColor || null !== $bgColor) {
            $text = AEC::color($text, $fgColor, $bgColor);
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
    public function writeln($text = '', $fgColor = null, $bgColor = null)
    {
        $this->write("{$text}\n", $fgColor, $bgColor);
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
        $this->write("{$text}\n", 'brightBlack');
    }

    /**
     * Debug
     *
     * @param string $text
     */
    public function debug($text)
    {
        $this->write("{$text}\n", 'white');
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
