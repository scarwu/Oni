<?php
/**
 * Read Task
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace CLIApp\Task;

use Oni\CLI\Task;

class ReadTask extends Task
{
    public function run()
    {
        // $this->io->write("What is your name? ");
        // $name = $this->io->read();

    	// or

        $name = $this->io->ask('What is your name? ');

        $this->io->log("Hi, {$name}!");
    }
}
