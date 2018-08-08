<?php
/**
 * Help Task
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace CLIApp\Main;

use Oni\CLI\Task;

class HelpTask extends Task
{
    public function run()
    {
        $this->out->info('Try above tasks:');
        $this->out->info('    ./boot.php help');
        $this->out->info('    ./boot.php read');
        $this->out->info('    ./boot.php color');
        $this->out->info('    ./boot.php parse');
    }
}
