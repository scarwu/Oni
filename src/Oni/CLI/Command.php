<?php
/**
 * Command
 *
 * @package     Oni
 * @author      ScarWu
 * @copyright   Copyright (c) ScarWu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace Oni\CLI;

use Exception;

abstract class Command
{
    /**
     * @var array
     */
    private static $_arguments = [];

    /**
     * @var array
     */
    private static $_options = [];

    /**
     * @var array
     */
    private static $_configs = [];

    /**
     * @var string
     */
    private static $_prefix = null;

    /**
     * @var string
     */
    protected static $_namespace = null;

    /**
     * Execute before run
     */
    public function up() {}

    /**
     * Execute after run
     */
    public function down() {}

    /**
     * Execute run
     */
    abstract public function run();

    /**
     * Initialize
     */
    final public function init()
    {
        if (null !== self::$_prefix) {
            return false;
        }

        // Parse Input Command
        $this->parseCommand();

        // Find Command
        list($class_name, self::$_arguments) = $this->findCommand(self::$_prefix, self::$_arguments);

        if ($class_name) {
            $class = new $class_name;

            if (false !== $class->up()) {
                $class->run();
            }

            $class->down();
        } else {
            if (false !== $this->up()) {
                $this->run();
            }

            $this->down();
        }
    }

    /**
     * Parse Command
     */
    private function parseCommand()
    {
        $config_regex_rule = '/^-{2}(\w+(?:-\w+)?)(?:=(.+))?/';
        $option_regex_rule = '/^-{1}(\w+)/';

        $argv = array_slice($_SERVER['argv'], 1);

        // arguments
        while ($argv) {
            if (preg_match($config_regex_rule, $argv[0])) {
                break;
            }

            if (preg_match($option_regex_rule, $argv[0])) {
                break;
            }

            self::$_arguments[] = array_shift($argv);
        }

        // options & configs
        while ($value = array_shift($argv)) {
            if (preg_match($config_regex_rule, $value, $match)) {
                self::$_configs[$match[1]] = isset($match[2]) ? $match[2] : null;
            }

            if (preg_match($option_regex_rule, $value, $match)) {
                self::$_options[$match[1]] = null;

                if (isset($argv[0])) {
                    if (preg_match($config_regex_rule, $argv[0])) {
                        continue;
                    }

                    if (preg_match($option_regex_rule, $argv[0])) {
                        continue;
                    }

                    self::$_options[$match[1]] = array_shift($argv);
                }
            }
        }

        self::$_prefix = get_class($this);
    }

    /**
     * Find Command
     *
     * @param string prefix
     * @param array arguments
     *
     * @return array
     */
    final protected function findCommand($prefix, $arguments)
    {
        $error_return = [
            false,
            $arguments
        ];

        $pattern = '/^' . str_replace('\\', '\\\\', self::$_namespace) . '\\\\(\w+)Command$/';

        if (!preg_match($pattern, $prefix, $match)) {
            return $error_return;
        }

        $class_name_list = [];
        $class_name_list[] = $match[1];

        if (count($arguments) > 0) {
            // Find Exists Class
            while ($arguments) {
                if (!preg_match('/^([a-zA-Z]+)$/', $arguments[0])) {
                    break;
                }

                $class_name = self::$_namespace . '\\';
                $class_name .= implode('\\', $class_name_list) . '\\';
                $class_name .= ucfirst($arguments[0]) . 'Command';

                try {
                    if (class_exists($class_name)) {
                        $class_name_list[] = ucfirst($arguments[0]);

                        array_shift($arguments);
                    }
                } catch (Exception $e) {
                    break;
                }
            }
        }

        if (count($class_name_list) > 1) {
            return [
                self::$_namespace . '\\' . implode('\\', $class_name_list) . 'Command',
                $arguments
            ];
        }

        return $error_return;
    }

    /**
     * Get Options
     *
     * @return array
     */
    final protected function getArguments($index = null)
    {
        if (null !== $index) {
            if (array_key_exists($index, self::$_arguments)) {
                return self::$_arguments[$index];
            } else {
                return false;
            }
        }

        return self::$_arguments;
    }

    /**
     * Get Options
     *
     * @return array
     */
    final protected function getOptions($option = null)
    {
        if (null !== $option) {
            if (array_key_exists($option, self::$_options)) {
                return self::$_options[$option];
            } else {
                return false;
            }
        }

        return self::$_options;
    }

    /**
     * Get Configs
     *
     * @return mixed
     */
    final protected function getConfigs($config = null)
    {
        if (null !== $config) {
            if (array_key_exists($config, self::$_configs)) {
                return self::$_configs[$config];
            } else {
                return false;
            }
        }

        return self::$_configs;
    }

    /**
     * Has Arguments
     *
     * @return boolean
     */
    final protected function hasArguments()
    {
        return count(self::$_arguments) > 0;
    }

    /**
     * Has Options
     *
     * @return boolean
     */
    final protected function hasOptions($option = null)
    {
        if (null !== $option) {
            return array_key_exists($option, self::$_options);
        }

        return count(self::$_options) > 0;
    }

    /**
     * Has Configs
     *
     * @return boolean
     */
    final protected function hasConfigs($config = null)
    {
        if (null !== $config) {
            return array_key_exists($config, self::$_configs);
        }

        return count(self::$_configs) > 0;
    }
}
