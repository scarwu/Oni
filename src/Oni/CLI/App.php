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

use Oni\Basic;
use Oni\Loader;
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
    public function run()
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
    private function loadTask()
    {
        $namespace = $this->getAttr('task/namespace');
        $path = $this->getAttr('task/path');
        $params = $this->io->getArguments();

        while (0 < sizeof($params)) {
            $param = $params[0];
            $param = ucfirst($param);

            if (false === file_exists("{$path}/{$param}")
                && false === file_exists("{$path}/{$param}Task.php")
            ) {
                break;
            }

            $path = "{$path}/{$param}";
            $namespace = "{$namespace}\\{$param}";

            array_shift($params);
        }

        // Rewrite Task
        if (false === file_exists("{$path}Task.php")) {
            $namespace = $this->getAttr('task/namespace');
            $path = $this->getAttr('task/path');
            $handler = ucfirst($this->getAttr('task/default/handler'));

            if (false === file_exists("{$path}/{$handler}Task.php")) {
                return false;
            }

            $namespace = "{$namespace}\\{$handler}Task";
        } else {
            $namespace = "{$namespace}Task";
        }

        $instance = new $namespace($this->io);

        if (false !== $instance->up()) {
            $instance->run($params);
        }

        $instance->down();

        return true;
    }
}
