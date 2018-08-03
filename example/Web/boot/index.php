<?php
/**
 * Bootstrap Example
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

// Set Default Time Zone
date_default_timezone_set('Etc/UTC');

$root = __DIR__ . '/..';

// Require Composer Autoloader
require "{$root}/../../vendor/autoload.php";

// New Oni Web Application Instance
$app = new Oni\Web\App();

$app->setAttr('name', 'WebApp');
$app->setAttr('controller', "{$root}/controllers");
$app->setAttr('model', "{$root}/models");
$app->setAttr('view', "{$root}/views");
$app->setAttr('static', "{$root}/static");
$app->setAttr('cache', "{$root}/cache");

$app->run();
