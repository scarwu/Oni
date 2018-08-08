<?php
/**
 * CLI Application
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace Oni\CLI;

use Exception;
use Oni\Basic;
use Oni\CLI\In;
use Oni\CLI\Out;

class App extends Basic
{
    /**
     * @var array
     */
    private static $_namespace_list = [];

    /**
     * @var object
     */
    protected $in = null;

    /**
     * @var object
     */
    protected $out = null;

    /**
     * Construct
     */
    public function __construct() {
        $this->_attr = [
            'name' => 'OniApp',
            'task' => null,     // Requied
            'task/default' => 'Main'
        ];

        $this->in = In::init();
        $this->out = Out::init();
    }

    /**
     * Run
     */
    public function run()
    {
        spl_autoload_register('self::loadTask');

        if (null !== self::$_prefix) {
            return false;
        }

        // Parse Input Task
        $this->parseTask();

        // Find Task
        list($class_name, self::$_arguments) = $this->findTask(self::$_prefix, self::$_arguments);

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
     * Load Task
     *
     * @param string $class_name
     *
     * @return bool
     */
    private function loadTask($class_name)
    {
        $class_name = trim($class_name, '\\');

        foreach (self::$_namespace_list as $namespace => $path_list) {
            $pattern = '/^' . str_replace('\\', '\\\\', $namespace) . '/';

            if (!preg_match($pattern, $class_name)) {
                continue;
            }

            $class_name = str_replace($namespace, '', trim($class_name, '\\'));
            $class_name = str_replace('\\', '/', trim($class_name, '\\'));

            foreach ($path_list as $path) {
                if (!file_exists("{$path}/{$class_name}.php")) {
                    continue;
                }

                require "{$path}/{$class_name}.php";

                return true;
            }
        }

        throw new Exception("Class: {$class_name} is not found.");
    }

    /**
     * Parse Task
     */
    private function parseTask()
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
     * Find Task
     *
     * @param string $prefix
     * @param array $arguments
     *
     * @return array
     */
    final protected function findTask($prefix, $arguments)
    {
        $error_return = [
            false,
            $arguments
        ];

        $pattern = '/^' . str_replace('\\', '\\\\', self::$_namespace) . '\\\\(\w+)Task$/';

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
                $class_name .= ucfirst($arguments[0]) . 'Task';

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
                self::$_namespace . '\\' . implode('\\', $class_name_list) . 'Task',
                $arguments
            ];
        }

        return $error_return;
    }
}
