<?php
/**
 * Bootstrap Example 
 * 
 * @package     Oni
 * @author      ScarWu
 * @copyright   Copyright (c) 2014, ScarWu (http://scar.simcz.tw/)
 * @link        http://github.com/scarwu/Oni
 */

$root = realpath(dirname(__FILE__) . '/../..');

require "$root/vendor/autoload.php";

$app = new Oni\App();

$app->set('name', 'Example');

$app->enable('api', "$root/example/Api");
$app->enable('model', "$root/example/Model");
$app->enable('template', "$root/example/Template");
$app->enable('static', "$root/example/Static");
$app->enable('cache', "$root/example/Cache");

$app->run();
