<?php
/**
 * Read Task
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace CLIApp\Main;

use Oni\CLI\Task;

class ReadTask extends Task
{
    public function run()
    {
        // IO::write("What is your name? ");
        // $name = IO::read();

    	// or

        $name = $this->in->ask("What is your name? ");

        $this->out->log("Hi, $name!");
    }
}
