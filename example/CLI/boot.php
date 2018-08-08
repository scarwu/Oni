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

// New Oni CLI Application Instance
$app = new Oni\CLI\App();

$app->setAttr('namespace', 'CLIApp');
$app->setAttr('task/namespace', 'CLIApp\Task');
$app->setAttr('task/path', "{$root}/tasks");
$app->setAttr('task/default', 'Help');

$app->run();
