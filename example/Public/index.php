<?php

$root = realpath(dirname(__FILE__) . '/../..');

require "$root/vendor/autoload.php";

$app = new Oni\App();

$app->setDev(true);

$app->setName('Example')->setPath([
    'api' => "$root/example/Api",
    'model' => "$root/example/Model",
    'template' => "$root/example/Template",
    'static' => "$root/example/Static"
])->run();