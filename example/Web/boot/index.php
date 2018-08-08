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

$app->setAttr('namespace', 'WebApp');
$app->setAttr('controller/namespace', 'WebApp\Controller');
$app->setAttr('controller/path', "{$root}/controllers");
$app->setAttr('model/namespace', 'WebApp\Model');
$app->setAttr('model/path', "{$root}/models");
$app->setAttr('view/path', "{$root}/views");
$app->setAttr('static/path', "{$root}/static");
$app->setAttr('cache/path', "{$root}/cache");

$app->run();
