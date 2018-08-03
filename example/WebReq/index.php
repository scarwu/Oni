<?php
/**
 * Oni\Web\Req Example
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
require "{$root}/../vendor/autoload.php";

use Oni\Web\Req;

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
