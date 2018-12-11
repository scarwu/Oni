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
        $this->io->write('X', 'yellow');
        $this->io->write('X', 'blue');
        $this->io->write('X', 'magenta');
        $this->io->write('X', 'cyan');
        $this->io->write('X', 'white');
        $this->io->write('X', 'default');
        $this->io->write('X', 'brightBlack');
        $this->io->write('X', 'brightRed');
        $this->io->write('X', 'brightGreen');
        $this->io->write('X', 'brightYellow');
        $this->io->write('X', 'brightBlue');
        $this->io->write('X', 'brightMagenta');
        $this->io->write('X', 'brightCyan');
        $this->io->write('X', 'brightWhite');

        $this->io->writeln();

        // Background Colors Only
        $this->io->write('X', null, 'black');
        $this->io->write('X', null, 'red');
        $this->io->write('X', null, 'green');
        $this->io->write('X', null, 'yellow');
        $this->io->write('X', null, 'blue');
        $this->io->write('X', null, 'magenta');
        $this->io->write('X', null, 'cyan');
        $this->io->write('X', null, 'white');
        $this->io->write('X', null, 'default');
        $this->io->write('X', null, 'brightBlack');
        $this->io->write('X', null, 'brightRed');
        $this->io->write('X', null, 'brightGreen');
        $this->io->write('X', null, 'brightYellow');
        $this->io->write('X', null, 'brightBlue');
        $this->io->write('X', null, 'brightMagenta');
        $this->io->write('X', null, 'brightCyan');
        $this->io->write('X', null, 'brightWhite');

        $this->io->writeln();

        // Text & Background Colors
        $this->io->write('X', 'black',          'brightWhite');
        $this->io->write('X', 'red',            'brightCyan');
        $this->io->write('X', 'green',          'brightMagenta');
        $this->io->write('X', 'yellow',         'brightBlue');
        $this->io->write('X', 'blue',           'brightYellow');
        $this->io->write('X', 'magenta',        'brightGreen');
        $this->io->write('X', 'cyan',           'brightRed');
        $this->io->write('X', 'white',          'brightBlack');
        $this->io->write('X', 'default',        'default');
        $this->io->write('X', 'brightBlack',    'white');
        $this->io->write('X', 'brightRed',      'cyan');
        $this->io->write('X', 'brightGreen',    'magenta');
        $this->io->write('X', 'brightYellow',   'blue');
        $this->io->write('X', 'brightBlue',     'yellow');
        $this->io->write('X', 'brightMagenta',  'green');
        $this->io->write('X', 'brightCyan',     'red');
        $this->io->write('X', 'brightWhite',    'black');

        $this->io->writeln();
    }
}