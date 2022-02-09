<?php
/**
 * Task
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace Oni\CLI;

use Oni\Core\Basic;
use Oni\CLI\IO;

abstract class Task extends Basic
{
    /**
     * @var object
     */
    protected $io = null;

    /**
     * Initializer
     */
    final public function init()
    {
        $this->io = $this->initDI('io', function () {
            return IO::init();
        });
    }

    /**
     * @var array
     */
    private $_arguments = [];

    /**
     * @var array
     */
    private $_options = [];

    /**
     * @var array
     */
    private $_configs = [];

    /**
     * Up
     *
     * Execute before run
     */
    public function up() {}

    /**
     * Down
     *
     * Execute after run
     */
    public function down() {}

    /**
     * Run
     */
    abstract public function run();
}
