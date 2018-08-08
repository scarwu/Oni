<?php
/**
 * Main Task
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace CLIApp;

use Oni\CLI\Task;

class MainTask extends Task
{
    public function run()
    {
        IO::warning('Call Default Task: Help');

        // Call Help Task
        (new \CLIApp\Main\HelpTask)->run();
    }
}
