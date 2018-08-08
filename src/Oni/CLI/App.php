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
use Oni\CLI\IO;

class App extends Basic
{
    /**
     * @var object
     */
    protected $io = null;

    /**
     * @var array
     */
    private $_namespace_list = [];

    /**
     * Construct
     */
    public function __construct()
    {
        // Set Default Attributes
        $this->_attr = [
            'namespace' => 'OniApp',
            'task/namespace' => null,   // Requied
            'task/path' => null,        // Requied
            'task/default' => 'Main'
        ];

        $this->io = IO::init();

        // Namespace Autoload Register
        spl_autoload_register(function ($class_name) {
            $class_name = trim($class_name, '\\');

            foreach ($this->_namespace_list as $namespace => $path_list) {
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

            return false;
        });
    }

    /**
     * Register Namespace
     *
     * @param string $namespace
     * @param string $path
     */
    public function registerNamespace($namespace, $path)
    {
        $namespace = trim($namespace, '\\');
        $path = rtrim($path, '/');

        if (!isset($this->_namespace_list[$namespace])) {
            $this->_namespace_list[$namespace] = [];
        }

        $this->_namespace_list[$namespace][] = $path;
    }

    /**
     * Run
     */
    public function run()
    {
        // Register Task Classes
        $namespace = $this->getAttr('task/namespace');
        $path = $this->getAttr('task/path');

        if (null !== $namespace && null !== $path) {
            $this->registerNamespace($namespace, $path);
        }

        // Load Task
        if (null !== $this->getAttr('task/namespace')
            && null !== $this->getAttr('task/path')
            && $this->loadTask()) {

            return true;
        }
    }

    /**
     * Load Task
     *
     * @return bool
     */
    private function loadTask()
    {
        $namespace = $this->getAttr('task/namespace');
        $path = $this->getAttr('task/path');
        $segments = $this->io->getArguments();

        // Set Deafult Task
        if (0 === sizeof($segments)) {
            $segments[] = $this->getAttr('task/default');
        }

        foreach ($segments as $segment) {
            $segment = ucfirst($segment);

            $path = "{$path}/{$segment}";
            $namespace = "{$namespace}\\{$segment}";
        }

        if (false === file_exists("{$path}Task.php")) {
            throw new Exception("Task is not found.");
        }

        // New Task Instance
        $namespace = "{$namespace}Task";
        $instance = new $namespace($this->io);

        if (false !== $instance->up()) {
            $instance->run();
        }

        $instance->down();

        return true;
    }
}
