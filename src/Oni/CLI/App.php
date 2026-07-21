<?php
declare(strict_types=1);
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
use Oni\Core\Basic;
use Oni\Core\Loader;
use Oni\CLI\IO;

class App extends Basic
{
    /**
     * @var array
     */
    protected array $_attr = [
        // Required
        'task/namespace'        => null,
        'task/path'             => null,

        // Optional
        'router/event/up'       => null,
        'router/event/down'     => null,
        'router/task/default'   => 'main',
        'router/task/error'     => 'error'
    ];

    /**
     * @var IO
     */
    protected IO $io;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->io = IO::init();
    }

    /**
     * Up Function
     *
     * @return bool
     */
    private function up(): bool
    {
        $namespace = $this->getAttr('task/namespace');
        $path = $this->getAttr('task/path');

        if (false === is_string($namespace) || false === is_string($path)) {
            throw new Exception('oni:exception:namespaceOrPathNotSet');
        }

        // Register Task Classes & Load
        Loader::append($namespace, $path);

        $upEvent = $this->getAttr('router/event/up');

        if (true === is_callable($upEvent)) {
            $upEvent();
        }

        return true;
    }

    /**
     * Down Function
     *
     * @return bool
     */
    private function down(): bool
    {
        $downEvent = $this->getAttr('router/event/down');

        if (true === is_callable($downEvent)) {
            $downEvent();
        }

        return true;
    }

    /**
     * Run
     *
     * @return bool
     */
    public function run(): bool
    {
        if (false === $this->up()) {
            return false;
        }

        if (false === $this->loadTask()) {
            return false;
        }

        if (false === $this->down()) {
            return false;
        }

        return true;
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

        if (false === is_string($namespace) || false === is_string($path)) {
            throw new Exception('oni:exception:namespaceOrPathNotSet');
        }

        $params = $this->io->getArguments();
        $currentPath = null;

        while (0 < count($params)) {
            $tempPath = ucfirst($params[0]);
            $tempPath = (true === is_string($currentPath)) ? "{$currentPath}/{$tempPath}" : $tempPath;

            if (false === file_exists("{$path}/{$tempPath}")
                && false === file_exists("{$path}/{$tempPath}Task.php")
            ) {
                break;
            }

            $currentPath = $tempPath;

            array_shift($params);
        }

        // Rewrite Task
        if (false === is_string($currentPath)) {
            $taskName = ucfirst($this->getAttr('router/task/default'));

            if (false === is_string($taskName) || false === file_exists("{$path}/{$taskName}Task.php")) {
                $taskName = ucfirst($this->getAttr('router/task/error'));
            }

            if (false === is_string($taskName) || false === file_exists("{$path}/{$taskName}Task.php")) {
                return false;
            }

            $currentPath = $taskName;
        }

        $className = implode('\\', explode('/', $currentPath));
        $className = "{$namespace}\\{$className}Task";

        $instance = new $className();

        // Task Flow
        if (false === $instance->up()) {
            return false;
        }

        if (false === $instance->run($params)) {
            return false;
        }

        if (false === $instance->down()) {
            return false;
        }

        return true;
    }
}
