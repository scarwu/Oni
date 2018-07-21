<?php
/**
 * Oni\Req Example
 *
 * @package     Oni
 * @author      ScarWu
 * @copyright   Copyright (c) 2014-2015, ScarWu (http://scar.simcz.tw/)
 * @link        http://github.com/scarwu/Oni
 */

// Set Default Time Zone
date_default_timezone_set('Etc/UTC');

$root = realpath(__DIR__ . '/../..');

// Require Composer Autoloader
require "$root/vendor/autoload.php";

use Oni\Req;

$req = Req::init();

var_dump([
    'method' => $req->method(),
    'type' => $req->contentType(),
    'path' => $req->path(),
    'urlParams' => $req->urlParams(),
    'content' => $req->content(),
    'file' => $req->file(),
    'header' => $req->header()
]);
