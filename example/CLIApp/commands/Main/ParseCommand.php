<?php
/**
 * Command Parse
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace CLIApp\Main;

use Oni\CLI\Command;
use Oni\CLI\IO;

class ParseCommand extends Command
{
    public function run()
    {
        if ($this->hasArguments()) {
            IO::debug('Arguments:');
            var_dump($this->getArguments());
        }

        if ($this->hasOptions()) {
            IO::debug('Options:');
            var_dump($this->getOptions());
        }

        if ($this->hasConfigs()) {
            IO::debug('Configs:');
            var_dump($this->getConfigs());
        }
    }
}
