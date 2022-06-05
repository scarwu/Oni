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

use Oni\Core\Basic;
use Oni\CLI\Helper\ANSIEscapeCode as AEC;

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

                if (true === isset($argv[0])) {
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
    public static function init(): object
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
     * @return mixed
     */
    public function getArguments(int $index = null)
    {
        if (true === is_integer($index)) {
            if (true === array_key_exists($index, $this->_arguments)) {
                return $this->_arguments[$index];
            } else {
                return null;
            }
        }

        return $this->_arguments;
    }

    /**
     * Has Arguments
     *
     * @return bool
     */
    public function hasArguments(): bool
    {
        return sizeof($this->_arguments) > 0;
    }

    /**
     * Get Options
     *
     * @return string $key
     *
     * @return mixed
     */
    public function getOptions(?string $key = null)
    {
        if (true === is_string($key)) {
            if (true === array_key_exists($key, $this->_options)) {
                return $this->_options[$key];
            } else {
                return null;
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
    public function hasOptions(?string $key = null): bool
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
     * @return mixed
     */
    public function getConfigs(?string $key = null)
    {
        if (true === is_string($key)) {
            if (true === array_key_exists($key, $this->_configs)) {
                return $this->_configs[$key];
            } else {
                return null;
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
    public function hasConfigs(?string $key = null): bool
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
    public function read(): string
    {
        return trim(fgets(STDIN));
    }

    /**
     * Ask
     *
     * @param string $text
     * @param callable $callback
     * @param string $fgColor
     * @param string $bgColor
     *
     * @return string|null
     */
    public function ask(string $text, ?callable $callback = null, ?string $fgColor = null, ?string $bgColor = null): ?string
    {
        if (null === $callback) {
            $callback = function() {
                return null;
            };
        }

        do {
            $this->write($text, $fgColor, $bgColor);
        } while (false === $callback($answer = $this->read()));

        return $answer;
    }

    /**
     * Menu Select
     *
     * @param array $options
     *
     * @return int
     */
    public function menuSelect(array $options): int
    {
        $totalIndex = count($options);
        $selectedIndex = 0;
        $isBreakLoop = false;
        $isFirstLoop = true;
        $char = null;

        $wWidth = (int) exec('tput cols');
        $wHeight = (int) exec('tput lines');

        readline_callback_handler_install('', function() {});

        // Set Cursor is Hide
        $this->write(AEC::cursorHide());

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

            if (true === $isBreakLoop) {
                break;
            }

            // Set Cursor Prev
            if (false === $isFirstLoop) {
                $this->write(AEC::cursorPrev($totalIndex - 1));
            } else {
                $isFirstLoop = false;
            }

            // Get Skip Index
            $skipIndex = $selectedIndex < $wHeight
                ? 0 : $selectedIndex - $wHeight + 1;

            // Get Current Options
            $currentOptions = array_slice($options, $skipIndex, $wHeight);

            // Print Menu
            $this->write(implode("\n", array_map(function ($option, $currentIndex) use ($selectedIndex, $skipIndex) {
                $padding = (($selectedIndex - $skipIndex) === $currentIndex)
                    ? '> ' : '  ';

                return AEC::CSI . "2K{$padding}{$option}";
            }, $currentOptions, array_keys($currentOptions))));

        } while ($char = stream_get_contents(STDIN, 1));

        // Set Cursor is Show
        $this->writeln(AEC::cursorShow());

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
    public function write($text, $fgColor = null, $bgColor = null): void
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
    public function writeln($text = '', $fgColor = null, $bgColor = null): void
    {
        $this->write("{$text}\n", $fgColor, $bgColor);
    }

    /**
     * Error
     *
     * @param string $text
     */
    public function error(string $text): void
    {
        $this->writeln($text, 'red');
    }

    /**
     * Warning
     *
     * @param string $text
     */
    public function warning(string $text): void
    {
        $this->writeln($text, 'yellow');
    }

    /**
     * Notice
     *
     * @param string $text
     */
    public function notice(string $text): void
    {
        $this->writeln($text, 'green');
    }

    /**
     * Info
     *
     * @param string $text
     */
    public function info(string $text): void
    {
        $this->writeln($text, 'brightBlack');
    }

    /**
     * Debug
     *
     * @param string $text
     */
    public function debug(string $text): void
    {
        $this->writeln($text, 'white');
    }

    /**
     * Log
     *
     * @param string $text
     */
    public function log(string $text): void
    {
        $this->writeln($text);
    }
}
