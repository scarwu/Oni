<?php
/**
 * Color Task
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace CLIApp\Main;

use Oni\CLI\Task;

class ColorTask extends Task
{
    public function run()
    {
        // Text Colors Only
        $this->out->write('X', 'black');
        $this->out->write('X', 'red');
        $this->out->write('X', 'green');
        $this->out->write('X', 'brown');
        $this->out->write('X', 'blue');
        $this->out->write('X', 'purple');
        $this->out->write('X', 'cyan');
        $this->out->write('X', 'light_gray');

        $this->out->write('X', 'dark_gray');
        $this->out->write('X', 'light_red');
        $this->out->write('X', 'light_green');
        $this->out->write('X', 'yellow');
        $this->out->write('X', 'light_blue');
        $this->out->write('X', 'light_purple');
        $this->out->write('X', 'light_cyan');
        $this->out->write('X', 'white');

        $this->out->writeln();

        // Background Colors Only
        $this->out->write('X', null, 'black');
        $this->out->write('X', null, 'red');
        $this->out->write('X', null, 'green');
        $this->out->write('X', null, 'brown');
        $this->out->write('X', null, 'blue');
        $this->out->write('X', null, 'purple');
        $this->out->write('X', null, 'cyan');
        $this->out->write('X', null, 'light_gray');

        $this->out->write('X', null, 'dark_gray');
        $this->out->write('X', null, 'light_red');
        $this->out->write('X', null, 'light_green');
        $this->out->write('X', null, 'yellow');
        $this->out->write('X', null, 'light_blue');
        $this->out->write('X', null, 'light_purple');
        $this->out->write('X', null, 'light_cyan');
        $this->out->write('X', null, 'white');

        $this->out->writeln();

        // Text & Background Colors
        $this->out->write('X', 'white', 'black');
        $this->out->write('X', 'light_cyan', 'red');
        $this->out->write('X', 'light_purple', 'green');
        $this->out->write('X', 'light_blue', 'brown');
        $this->out->write('X', 'yellow', 'blue');
        $this->out->write('X', 'light_green', 'purple');
        $this->out->write('X', 'light_red', 'cyan');
        $this->out->write('X', 'dark_gray', 'light_gray');

        $this->out->write('X', 'light_gray', 'dark_gray');
        $this->out->write('X', 'cyan', 'light_red');
        $this->out->write('X', 'purple', 'light_green');
        $this->out->write('X', 'blue', 'yellow');
        $this->out->write('X', 'brown', 'light_blue');
        $this->out->write('X', 'green', 'light_purple');
        $this->out->write('X', 'red', 'light_cyan');
        $this->out->write('X', 'black', 'white');

        $this->out->writeln();
    }
}
