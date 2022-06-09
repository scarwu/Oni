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
        'task/namespace'        => null,    // Requied
        'task/path'             => null,    // Requied
        'task/default/handler'  => 'Main',
        'task/error/handler'    => 'Main'
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
     * Run
     */
    public function run(): bool
    {
        // Register Task Classes & Load
        $namespace = $this->getAttr('task/namespace');
        $path = $this->getAttr('task/path');

        if (null !== $namespace && null !== $path) {
            Loader::append($namespace, $path);

            if (true === $this->loadTask()) {
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
            $handler = ucfirst($this->getAttr('task/default/handler'));

            if (false === file_exists("{$path}/{$handler}Task.php")) {
                return false;
            }

            $currentPath = $handler;
        }

        // Task Flow
        $currentNamaspece = implode('\\', explode('/', $currentPath));
        $currentNamaspece = "{$namespace}\\{$currentNamaspece}Task";

        $instance = new $currentNamaspece();

        if (false !== $instance->up()) {
            $instance->run($params);
        }

        $instance->down();

        return true;
    }
}
