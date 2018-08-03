<?php
/**
 * Command Color
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace CLIApp\Main;

use Oni\CLI\Command;
use Oni\CLI\IO;

class ColorCommand extends Command
{
    public function run()
    {
        // Text Colors Only
        IO::write('X', 'black');
        IO::write('X', 'red');
        IO::write('X', 'green');
        IO::write('X', 'brown');
        IO::write('X', 'blue');
        IO::write('X', 'purple');
        IO::write('X', 'cyan');
        IO::write('X', 'light_gray');

        IO::write('X', 'dark_gray');
        IO::write('X', 'light_red');
        IO::write('X', 'light_green');
        IO::write('X', 'yellow');
        IO::write('X', 'light_blue');
        IO::write('X', 'light_purple');
        IO::write('X', 'light_cyan');
        IO::write('X', 'white');

        IO::writeln();

        // Background Colors Only
        IO::write('X', null, 'black');
        IO::write('X', null, 'red');
        IO::write('X', null, 'green');
        IO::write('X', null, 'brown');
        IO::write('X', null, 'blue');
        IO::write('X', null, 'purple');
        IO::write('X', null, 'cyan');
        IO::write('X', null, 'light_gray');

        IO::write('X', null, 'dark_gray');
        IO::write('X', null, 'light_red');
        IO::write('X', null, 'light_green');
        IO::write('X', null, 'yellow');
        IO::write('X', null, 'light_blue');
        IO::write('X', null, 'light_purple');
        IO::write('X', null, 'light_cyan');
        IO::write('X', null, 'white');

        IO::writeln();

        // Text & Background Colors
        IO::write('X', 'white', 'black');
        IO::write('X', 'light_cyan', 'red');
        IO::write('X', 'light_purple', 'green');
        IO::write('X', 'light_blue', 'brown');
        IO::write('X', 'yellow', 'blue');
        IO::write('X', 'light_green', 'purple');
        IO::write('X', 'light_red', 'cyan');
        IO::write('X', 'dark_gray', 'light_gray');

        IO::write('X', 'light_gray', 'dark_gray');
        IO::write('X', 'cyan', 'light_red');
        IO::write('X', 'purple', 'light_green');
        IO::write('X', 'blue', 'yellow');
        IO::write('X', 'brown', 'light_blue');
        IO::write('X', 'green', 'light_purple');
        IO::write('X', 'red', 'light_cyan');
        IO::write('X', 'black', 'white');

        IO::writeln();
    }
}
