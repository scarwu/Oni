<?php

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
