<?php
/**
 * Bootstrap Example 
 * 
 * @package     Oni
 * @author      ScarWu
 * @copyright   Copyright (c) 2014, ScarWu (http://scar.simcz.tw/)
 * @link        http://github.com/scarwu/Oni
 */

// Set Default Time Zone
date_default_timezone_set('Etc/UTC');

$root = realpath(dirname(__FILE__) . '/../..');

// Require Composer Autoloader
require "$root/vendor/autoload.php";

// New Oni Application Instance
$app = new Oni\App();

$app->set('controller', "$root/example/Controller");
$app->set('model', "$root/example/Model");
$app->set('view', "$root/example/View");
$app->set('static', "$root/example/Static");
$app->set('cache', "$root/example/Cache");

$app->run();
