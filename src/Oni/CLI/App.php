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
        $arguments = $this->io->getArguments();

        // Set Deafult Task
        if (0 === sizeof($arguments)) {
            $arguments[] = $this->getAttr('task/default');
        }

        while (0 < sizeof($arguments)) {
            $argument = $arguments[0];
            $argument = ucfirst($argument);

            if (false === file_exists("{$path}/{$argument}")
                && false === file_exists("{$path}/{$argument}Task.php")) {

                break;
            }

            $path = "{$path}/{$argument}";
            $namespace = "{$namespace}\\{$argument}";

            array_shift($arguments);
        }

        if (false === file_exists("{$path}Task.php")) {
            throw new Exception("Task is not found.");
        }

        $this->io->replaceArguments($arguments);

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
