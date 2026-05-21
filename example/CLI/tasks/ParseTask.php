<?php
declare(strict_types=1);
/**
 * Parse Task
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace CLIApp\Task;

use Oni\CLI\Task;

class ParseTask extends Task
{
    #[\Override]
    public function run(array $params = []): void
    {
        if (0 !== count($params)) {
            $this->io->debug('Params:');
            var_dump($params);
        }

        if ($this->io->hasArguments()) {
            $this->io->debug('Arguments:');
            var_dump($this->io->getArguments());
        }

        if ($this->io->hasOptions()) {
            $this->io->debug('Options:');
            var_dump($this->io->getOptions());
        }

        if ($this->io->hasConfigs()) {
            $this->io->debug('Configs:');
            var_dump($this->io->getConfigs());
        }
    }
}
