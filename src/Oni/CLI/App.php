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
     * Construct
     */
    public function __construct() {
        $this->_attr = [
            'name' => 'OniApp',
            'task' => null,     // Requied
            'task/default' => 'Main'
        ];

        $this->io = IO::init();
    }

    /**
     * Run
     */
    public function run()
    {
        // Load Task
        if ($this->_attr['task'] && $this->loadTask()) {
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
        $current_path = $this->getAttr('task');
        $current_namespace = $this->getAttr('name') . '\\Task';

        $segments = $this->io->getArguments();

        // Set Deafult Task
        if (0 === sizeof($segments)) {
            $segments[] = $this->getAttr('task/default');
        }

        foreach ($segments as $segment) {
            $segment = ucfirst($segment);

            $current_path = "{$current_path}/{$segment}";
            $current_namespace = "{$current_namespace}\\{$segment}";
        }

        if (false === file_exists("{$current_path}Task.php")) {
            throw new Exception("Task is not found.");
        }

        // Require Task
        require "{$current_path}Task.php";

        // New Task Instance
        $current_namespace = "{$current_namespace}Task";
        $instance = new $current_namespace($this->io);

        if (false !== $instance->up()) {
            $instance->run();
        }

        $instance->down();
    }
}
