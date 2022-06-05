<?php
/**
 * Help Task
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace CLIApp\Task;

use Oni\CLI\Task;

class HelpTask extends Task
{
    public function run()
    {
        $this->io->info('Try above tasks:');
        $this->io->info('    ./boot.php help');
        $this->io->info('    ./boot.php read');
        $this->io->info('    ./boot.php menu');
        $this->io->info('    ./boot.php color');
        $this->io->info('    ./boot.php parse');
    }
}
