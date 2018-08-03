<?php
/**
 * Command example
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace CLIApp;

use Oni\CLI\Command;
use Oni\CLI\IO;

class MainCommand extends Command
{
    public function __construct()
    {
        self::$_namespace = __NAMESPACE__;
    }

    public function run()
    {
        IO::warning('Call Default Command: Help');

        // Call Help Command
        (new \CLIApp\Main\HelpCommand)->run();
    }
}
