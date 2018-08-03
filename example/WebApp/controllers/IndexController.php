<?php
/**
 * Index Controller Example
 *
 * @package     Oni
 * @author      ScarWu
 * @copyright   Copyright (c) ScarWu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace WebApp\Controller;

use Oni\Web\Controller;
use Oni\Web\Req;
use Oni\Web\Res;

class IndexController extends Controller
{
    public function getAction()
    {
        Res::html('index', [
            'title' => 'Oni - A Simple REST Framework',
            'method' => Req::method(),
            'param' => implode('/', Req::param())
        ]);
    }
}