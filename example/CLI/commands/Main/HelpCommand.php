<?php
/**
 * Command Help
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace CLIApp\Main;

use Oni\CLI\Command;
use Oni\CLI\IO;

class HelpCommand extends Command
{
    public function run()
    {
        IO::info('Try above commands:');
        IO::info('    ./boot.php help');
        IO::info('    ./boot.php read');
        IO::info('    ./boot.php color');
        IO::info('    ./boot.php parse');
    }
}
