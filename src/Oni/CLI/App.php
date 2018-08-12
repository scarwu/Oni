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
use Oni\Loader;
use Oni\CLI\IO;

class App extends Basic
{
    /**
     * @var object
     */
    protected $io = null;

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
            Loader::append($namespace, $path);
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
        $params = $this->io->getArguments();

        while (0 < sizeof($params)) {
            $param = $params[0];
            $param = ucfirst($param);

            if (false === file_exists("{$path}/{$param}")
                && false === file_exists("{$path}/{$param}Task.php")) {

                break;
            }

            $path = "{$path}/{$param}";
            $namespace = "{$namespace}\\{$param}";

            array_shift($params);
        }

        // Rewrite Task
        if (false === file_exists("{$path}Task.php")) {
            $default = $this->getAttr('task/default');
            $default = ucfirst($default);

            if (false === file_exists("{$path}/{$default}Task.php")) {
                return false;
            }

            $namespace = "{$namespace}\\{$default}";
        }

        // New Task Instance
        $namespace = "{$namespace}Task";
        $instance = new $namespace($this->io);

        if (false !== $instance->up()) {
            $instance->run($params);
        }

        $instance->down();

        return true;
    }
}
