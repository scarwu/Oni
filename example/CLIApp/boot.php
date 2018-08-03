#!/usr/bin/env php
<?php
/**
 * Bootstrap
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

// Set Default Time Zone
date_default_timezone_set('Etc/UTC');

$root = __DIR__;

// Require Composer Autoloader
require "{$root}/../../vendor/autoload.php";

// Register Oni CLI Autoloader
Oni\CLI\Loader::set('CLIApp', "{$root}/commands");
Oni\CLI\Loader::register();

(new CLIApp\MainCommand)->Init();
