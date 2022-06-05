<?php
/**
 * Menu Task
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace CLIApp\Task;

use Oni\CLI\Task;

class MenuTask extends Task
{
    public function run()
    {
        $count = $this->io->ask('Item counts of menu? [10] ', function ($value) {
            return true === (bool) preg_match('/^\d+$/', $value) || '' === $value;
        });

        if ('' !== $count) {
            $count = (int) $count;
        } else {
            $count = 10;
        }

        if (0 === $count) {
            $count = 10;
        }

        $lines = $this->io->ask('Display lines of menu? [3] ', function ($value) {
            return true === (bool) preg_match('/^\d+$/', $value) || '' === $value;
        });

        if ('' !== $lines) {
            $lines = (int) $lines;
        } else {
            $lines = 3;
        }

        if (0 === $lines) {
            $lines = 3;
        }

        if ($lines > $count) {
            $lines = $count;
        }

        $list = [];

        for ($index = 0; $index < $count; $index++) {
            $list[] = sprintf('[%3d]', $index) . ' ' . md5($index);
        }

        $this->io->writeln("Select Index");

        $index = $this->io->menuSelect($list, $lines);

        $this->io->log("You selected index is {$index}!");
    }
}
