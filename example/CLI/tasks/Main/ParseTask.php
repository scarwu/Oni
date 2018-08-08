<?php
/**
 * Parse Task
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace CLIApp\Main;

use Oni\CLI\Task;

class ParseTask extends Task
{
    public function run()
    {
        if ($this->hasArguments()) {
            $this->out->debug('Arguments:');
            var_dump($this->getArguments());
        }

        if ($this->hasOptions()) {
            $this->out->debug('Options:');
            var_dump($this->getOptions());
        }

        if ($this->hasConfigs()) {
            $this->out->debug('Configs:');
            var_dump($this->getConfigs());
        }
    }
}
