<?php
/**
 * Bootstrap Example
 *
 * @package     Oni
 * @author      ScarWu
 * @copyright   Copyright (c) ScarWu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

// Set Default Time Zone
date_default_timezone_set('Etc/UTC');

$root = __DIR__ . '/..';

// Require Composer Autoloader
require "{$root}/../../vendor/autoload.php";

// New Oni Web Application Instance
$app = new Oni\Web\App();

$app->set('name', 'WebApp');
$app->set('controller', "{$root}/controllers");
$app->set('model', "{$root}/models");
$app->set('view', "{$root}/views");
$app->set('static', "{$root}/static");
$app->set('cache', "{$root}/cache");

$app->run();
