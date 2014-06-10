<?php
/**
 * Index Controller Example 
 * 
 * @package     Oni
 * @author      ScarWu
 * @copyright   Copyright (c) 2014, ScarWu (http://scar.simcz.tw/)
 * @link        http://github.com/scarwu/Oni
 */

namespace OniApp\Controller;

use Oni\Controller;
use Oni\Req;
use Oni\Res;

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
