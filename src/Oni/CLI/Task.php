<?php
declare(strict_types=1);
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
     * Up: Execute before run
     *
     * @return bool
     */
    public function up(): bool
    {
        return true;
    }

    /**
     * Down: Execute after run
     *
     * @return bool
     */
    public function down(): bool
    {
        return true;
    }

    /**
     * Run: Execute the task
     *
     * @param array $params
     *
     * @return bool
     */
    abstract public function run(array $params = []): bool;
}
