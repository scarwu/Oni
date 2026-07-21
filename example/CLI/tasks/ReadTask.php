<?php
declare(strict_types=1);
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
    public function run(array $params = []): bool
    {
        $gender = $this->io->menuSelector('What is your gender?', [
            'male',
            'female',
            'other'
        ]);

        // $this->io->write("What is your name?");
        // $name = $this->io->read();

        // or

        $name = $this->io->ask('What is your name?', static function (string $value): bool {
            return '' !== $value;
        });

        if (0 === $gender) {
            $this->io->log("Hi, Mr.{$name}!");
        } else {
            $this->io->log("Hi, Ms.{$name}!");
        }

        return true;
    }
}
