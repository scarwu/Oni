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
     * Construct
     */
    public function __construct()
    {
        $this->io = IO::init();
    }

    /**
     * Up
     *
     * Execute before run
     */
    public function up(): mixed
    {
        return true;
    }

    /**
     * Down
     *
     * Execute after run
     */
    public function down(): void {}

    /**
     * Run
     *
     * @param array $params
     */
    abstract public function run(array $params = []): void;
}
