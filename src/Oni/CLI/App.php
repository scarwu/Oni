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

use Oni\Core\Basic;
use Oni\Core\Loader;
use Oni\CLI\IO;

class App extends Basic
{
    /**
     * @var array
     */
    protected $_attr = [
        'router/event/up'       => null,
        'router/event/down'     => null,
        'router/default/task'   => 'Main',
        // 'router/error/task'     => 'Main',

        'task/namespace'        => null,    // Requied
        'task/path'             => null    // Requied
    ];

    /**
     * @var object
     */
    protected $io = null;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->io = IO::init();
    }

    /**
     * Up Function
     */
    private function up()
    {
        // Register Task Classes & Load
        $namespace = $this->getAttr('task/namespace');
        $path = $this->getAttr('task/path');

        if (null !== $namespace && null !== $path) {
            Loader::append($namespace, $path);
        }

        $upEvent = $this->getAttr('router/event/up');

        if (true === is_callable($upEvent)) {
            return $upEvent();
        }

        return true;
    }

    /**
     * Down Function
     */
    private function down()
    {
        $downEvent = $this->getAttr('router/event/down');

        if (true === is_callable($downEvent)) {
            $downEvent();
        }
    }

    /**
     * Run
     */
    public function run(): bool
    {
        if (false !== $this->up()) {

            // Load Task to Handle
            if (true === $this->loadTask()) {
                $this->down();

                return true;
            }
        }

        return false;
    }

    /**
     * Load Task
     *
     * @return bool
     */
    private function loadTask(): bool
    {
        $namespace = $this->getAttr('task/namespace');
        $path = $this->getAttr('task/path');
        $params = $this->io->getArguments();

        $currentPath = null;

        while (0 < count($params)) {
            $tempPath = ucfirst($params[0]);
            $tempPath = (null !== $currentPath) ? "{$currentPath}/{$tempPath}" : $tempPath;

            if (false === file_exists("{$path}/{$tempPath}")
                && false === file_exists("{$path}/{$tempPath}Task.php")
            ) {
                break;
            }

            $currentPath = $tempPath;

            array_shift($params);
        }

        // Rewrite Task
        if (null === $currentPath) {
            $taskName = ucfirst($this->getAttr('router/default/task'));

            if (false === file_exists("{$path}/{$taskName}Task.php")) {
                return false;
            }

            $currentPath = $taskName;
        }

        // Task Flow
        $className = implode('\\', explode('/', $currentPath));
        $className = "{$namespace}\\{$className}Task";

        $instance = new $className();

        if (false !== $instance->up()) {
            $instance->run($params);
        }

        $instance->down();

        return true;
    }
}
