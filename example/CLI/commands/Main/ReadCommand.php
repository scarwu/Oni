<?php
/**
 * Command Read
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace CLIApp\Main;

use Oni\CLI\Command;
use Oni\CLI\IO;

class ReadCommand extends Command
{
    public function run()
    {
        // IO::write("What is your name? ");
        // $name = IO::read();

    	// or

        $name = IO::ask("What is your name? ");

        IO::log("Hi, $name!");
    }
}
