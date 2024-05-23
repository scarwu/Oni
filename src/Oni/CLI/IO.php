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

        // Pasre Commends
        while (0 !== count($argv)) {
            $value = array_shift($argv);

            // options
            if (true === (bool) preg_match($optionRegexRule, $value, $match)) {
                if (true === isset($argv[0])
                    && false === (bool) preg_match($configRegexRule, $argv[0])
                    && false === (bool) preg_match($optionRegexRule, $argv[0])
                ) {
                    $this->_options[$match[1]] = array_shift($argv);
                } else {
                    $this->_options[$match[1]] = null;
                }

                continue;
            }

            // configs
            if (true === (bool) preg_match($configRegexRule, $value, $match)) {
                $this->_configs[$match[1]] = isset($match[2]) ? $match[2] : null;

                continue;
            }

            // arguments
            $this->_arguments[] = $value;
        }

        // Set Ctrl Handler (PHP 7 >= 7.4.0, PHP 8)
        // sapi_windows_set_ctrl_handler(function () {
        //     switch ($event) {
        //     case PHP_WINDOWS_EVENT_CTRL_C:

        //         // Set Cursor is Show
        //         $this->writeln(AEC::cursorShow());

        //         // Remove Readline Callback
        //         readline_callback_handler_remove();

        //         break;
        //     }
        // });
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
            $this->write("{$text}\n> ", $fgColor, $bgColor);
        } while (false === $callback($answer = $this->read()));

        return $answer;
    }

    /**
     * Menu Selector
     *
     * @param array $text
     * @param array $options
     * @param int $specifyLimitLines
     *
     * @return int
     */
    public function menuSelector(string $text, array $options, ?int $specifyLimitLines = null): int
    {
        $totalIndex = count($options);

        if (0 === $totalIndex) {
            return null;
        }

        $offsetIndex = 0;
        $currentIndex = 0;
        $isSelectIndex = false;

        $wWidth = (int) exec('tput cols');
        $wHeight = (int) exec('tput lines');

        $limitLines = $totalIndex <= $wHeight
            ? $totalIndex : $wHeight;

        if (true === is_integer($specifyLimitLines)
            && $specifyLimitLines > 0
            && $specifyLimitLines < $limitLines
        ) {
            $limitLines = $specifyLimitLines;
        }

        $this->writeln($text);

        // Install Readline Callback
        readline_callback_handler_install('', function() {});

        // Set Cursor is Hide
        $this->write(AEC::cursorHide());

        while (true) {

            // Print Menu
            $list = [];

            foreach (array_slice($options, $offsetIndex, $limitLines) as $index => $option) {
                $cursor = (($currentIndex - $offsetIndex) === $index) ? '> ' : '  ';
                $list[] = AEC::CSI . "2K{$cursor}{$option}";
            }

            $this->write(implode("\n", $list));

            // Wait Typing
            $isMatched = false;

            while ($char = stream_get_contents(STDIN, 1)) {
                switch (ord($char)) {
                case AEC::KEY_CODE_ENTER:
                    $isSelectIndex = true;
                    $isMatched = true;

                    break;
                case AEC::KEY_CODE_UP:
                    $currentIndex--;
                    $isMatched = true;

                    break;
                case AEC::KEY_CODE_DOWN:
                    $currentIndex++;
                    $isMatched = true;

                    break;
                case AEC::KEY_CODE_PAGE_UP:
                    $currentIndex -= $limitLines;
                    $isMatched = true;

                    break;
                case AEC::KEY_CODE_PAGE_DOWN:
                    $currentIndex += $limitLines;
                    $isMatched = true;

                    break;
                case AEC::KEY_CODE_HOME:
                    $currentIndex = 0;
                    $isMatched = true;

                    break;
                case AEC::KEY_CODE_END:
                    $currentIndex = $totalIndex;
                    $isMatched = true;

                    break;
                }

                if (true === $isMatched) {
                    break;
                }
            }

            if (true === $isSelectIndex) {
                break;
            }

            // Set Selected Index
            if ($currentIndex < 0) {
                $currentIndex = 0;
            } elseif ($currentIndex >= $totalIndex) {
                $currentIndex = $totalIndex - 1;
            }

            // Set Skip Index
            if ($currentIndex < $offsetIndex) {
                $offsetIndex = $currentIndex;
            } else if ($currentIndex > $offsetIndex + $limitLines - 1) {
                $offsetIndex = $currentIndex - $limitLines + 1;
            }

            if (1 < $limitLines) {
                $this->write(AEC::cursorPrev($limitLines - 1)); // Set Cursor Prev
            } else {
                $this->write("\r"); // Set Return
            }
        }

        // Set Cursor is Show
        $this->writeln(AEC::cursorShow());

        // Remove Readline Callback
        readline_callback_handler_remove();

        return $currentIndex;
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
