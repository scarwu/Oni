<?php
/**
 * Color Task
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace CLIApp\Task;

use Oni\CLI\Task;

class ColorTask extends Task
{
    public function run()
    {
        // Text Colors Only
        $this->io->write('X', 'black');
        $this->io->write('X', 'red');
        $this->io->write('X', 'green');
        $this->io->write('X', 'brown');
        $this->io->write('X', 'blue');
        $this->io->write('X', 'purple');
        $this->io->write('X', 'cyan');
        $this->io->write('X', 'light_gray');

        $this->io->write('X', 'dark_gray');
        $this->io->write('X', 'light_red');
        $this->io->write('X', 'light_green');
        $this->io->write('X', 'yellow');
        $this->io->write('X', 'light_blue');
        $this->io->write('X', 'light_purple');
        $this->io->write('X', 'light_cyan');
        $this->io->write('X', 'white');

        $this->io->writeln();

        // Background Colors Only
        $this->io->write('X', null, 'black');
        $this->io->write('X', null, 'red');
        $this->io->write('X', null, 'green');
        $this->io->write('X', null, 'brown');
        $this->io->write('X', null, 'blue');
        $this->io->write('X', null, 'purple');
        $this->io->write('X', null, 'cyan');
        $this->io->write('X', null, 'light_gray');

        $this->io->write('X', null, 'dark_gray');
        $this->io->write('X', null, 'light_red');
        $this->io->write('X', null, 'light_green');
        $this->io->write('X', null, 'yellow');
        $this->io->write('X', null, 'light_blue');
        $this->io->write('X', null, 'light_purple');
        $this->io->write('X', null, 'light_cyan');
        $this->io->write('X', null, 'white');

        $this->io->writeln();

        // Text & Background Colors
        $this->io->write('X', 'white', 'black');
        $this->io->write('X', 'light_cyan', 'red');
        $this->io->write('X', 'light_purple', 'green');
        $this->io->write('X', 'light_blue', 'brown');
        $this->io->write('X', 'yellow', 'blue');
        $this->io->write('X', 'light_green', 'purple');
        $this->io->write('X', 'light_red', 'cyan');
        $this->io->write('X', 'dark_gray', 'light_gray');

        $this->io->write('X', 'light_gray', 'dark_gray');
        $this->io->write('X', 'cyan', 'light_red');
        $this->io->write('X', 'purple', 'light_green');
        $this->io->write('X', 'blue', 'yellow');
        $this->io->write('X', 'brown', 'light_blue');
        $this->io->write('X', 'green', 'light_purple');
        $this->io->write('X', 'red', 'light_cyan');
        $this->io->write('X', 'black', 'white');

        $this->io->writeln();
    }
}
